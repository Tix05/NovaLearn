import axios from './apiConfig';
import { getAdminToken, forceLogout } from './adminAuthService';
import { getAdminNiveaux } from './bibliothequeAdminService';

const API_URL = 'http://localhost:8000/api/agenda/admin';

const handleError = (error) => {
    let errorMessage = 'Une erreur est survenue';
    if (error.response) {
        if (error.response.status === 401) {
            errorMessage = 'Session expirée ou non autorisée';
            forceLogout();
        } else if (error.response.status === 403) {
            errorMessage = 'Accès interdit';
        } else if (error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        }
    } else if (error.request) {
        errorMessage = 'Le serveur ne répond pas';
    }
    throw new Error(errorMessage);
};

export const getAgendas = async () => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }
    try {
        const response = await axios.get(`${API_URL}`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        const items = response.data['hydra:member'] || [];
        return items.map(item => ({
            ...item,
            id: parseInt(item['@id'].split('/').pop()) || null,
            titre: item.titre || 'Sans titre',
            type: item.type || 'COURS',
            date: item.date || new Date().toISOString(),
            mentionId: item.mentionId || null,
            niveauId: item.niveauId || null,
            parcoursId: item.parcoursId || null,
            parcours: item.parcours || null,
        })).filter(item => item.id !== null);
    } catch (error) {
        console.error('Error in getAgendas:', error);
        handleError(error);
    }
};

export const createAgenda = async (agendaData, imageFile, videoFile, onProgress) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }
    try {
        const formData = new FormData();
        formData.append('titre', agendaData.titre);
        formData.append('description', agendaData.description);
        formData.append('date', agendaData.date.toISOString());
        formData.append('dateExpiration', agendaData.dateExpiration.toISOString());
        formData.append('type', agendaData.type.toUpperCase());
        if (agendaData.url) {
            formData.append('url', agendaData.url);
        }
        if (agendaData.mention) {
            formData.append('mention', agendaData.mention);
        }
        if (agendaData.niveau) {
            formData.append('niveau', agendaData.niveau);
        }
        if (agendaData.parcours) {
            console.log('Mapping parcours in createAgenda:', agendaData.parcours);
            const niveaux = await getAdminNiveaux(agendaData.mention);
            const selectedNiveau = niveaux.find(niveau => niveau.value === agendaData.niveau);
            const parcoursMatch = selectedNiveau?.parcours.find(p => p.value === agendaData.parcours);
            console.log('Parcours match in createAgenda:', parcoursMatch);
            formData.append('parcours', parcoursMatch ? parcoursMatch.id : null);
        }
        if (imageFile) {
            formData.append('image', imageFile);
        }
        if (videoFile) {
            formData.append('video', videoFile);
        }

        console.log('Sending formData:', formData);
        const response = await axios.post(`${API_URL}`, formData, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (onProgress) {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    onProgress(percentCompleted);
                }
            },
        });
        const item = response.data;
        return {
            ...item,
            id: parseInt(item['@id'].split('/').pop()) || null,
            titre: item.titre || 'Sans titre',
            type: item.type || 'COURS',
            date: item.date || new Date().toISOString(),
            mentionId: item.mentionId || null,
            niveauId: item.niveauId || null,
            parcoursId: item.parcoursId || null,
            parcours: item.parcours || null, // Ajouter pour gérer le nom du parcours
        };
    } catch (error) {
        console.error('Error in createAgenda:', error);
        handleError(error);
    }
};

export const updateAgenda = async (id, agendaData) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }
    try {
        const payload = {
            titre: agendaData.titre,
            description: agendaData.description,
        };

        if (agendaData.type !== 'EXAMEN') {
            if (agendaData.date) {
                payload.date = agendaData.date.toISOString();
            }
            if (agendaData.dateExpiration) {
                payload.dateExpiration = agendaData.dateExpiration.toISOString();
            }
            if (agendaData.url !== undefined) {
                payload.url = agendaData.url || null;
            }
            if (agendaData.mention) {
                payload.mention = agendaData.mention;
            }
            if (agendaData.niveau) {
                payload.niveau = agendaData.niveau;
            }
            if (agendaData.parcours !== undefined) {
                console.log('Mapping parcours:', agendaData.parcours);
                const niveaux = await getAdminNiveaux(agendaData.mention);
                const selectedNiveau = niveaux.find(niveau => niveau.value === agendaData.niveau);
                const parcoursMatch = selectedNiveau?.parcours.find(p => p.value === agendaData.parcours);
                console.log('Parcours match in updateAgenda:', parcoursMatch);
                payload.parcours = parcoursMatch ? parcoursMatch.id : null;
            }
        }

        console.log('Sending payload:', payload);
        const response = await axios.patch(`${API_URL}/${id}`, payload, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        const item = response.data;
        return {
            ...item,
            id: parseInt(item['@id'].split('/').pop()) || null,
            titre: item.titre || 'Sans titre',
            type: item.type || 'COURS',
            date: item.date || new Date().toISOString(),
            mentionId: item.mentionId || null,
            niveauId: item.niveauId || null,
            parcoursId: item.parcoursId || null,
            parcours: item.parcours || null, // Ajouter pour gérer le nom du parcours
        };
    } catch (error) {
        console.error('Error in updateAgenda:', error);
        handleError(error);
    }
};

export const uploadAgendaFile = async (id, imageFile, videoFile, onProgress) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const formData = new FormData();
        if (imageFile) {
            formData.append('image', imageFile);
        }
        if (videoFile) {
            formData.append('video', videoFile);
        }

        const response = await axios.post(`${API_URL}/${id}/upload`, formData, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (onProgress) {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    onProgress(percentCompleted);
                }
            },
        });
        return response.data;
    } catch (error) {
        handleError(error);
    }
};

export const deleteAgenda = async (id) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.delete(`${API_URL}/${id}`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        handleError(error);
    }
};