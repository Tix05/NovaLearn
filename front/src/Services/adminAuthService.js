import axios from './apiConfig';

const API_URL = 'http://localhost:8000/api';

export const adminLogin = async (email, password) => {
    try {
        const response = await axios.post(`${API_URL}/admin/login`, {
            email,
            password
        });

        if (response.data.token) {
            localStorage.setItem('admin', JSON.stringify(response.data));
        }

        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.message || 'Erreur lors de la connexion');
    }
};

export const getCurrentAdmin = () => {
    const admin = localStorage.getItem('admin');
    return admin ? JSON.parse(admin) : null;
};

export const getAdminToken = () => {
    const admin = getCurrentAdmin();
    return admin ? admin.token : null;
};

export const adminLogout = async () => {
    const token = getAdminToken();

    if (token) {
        try {
            await axios.post(`${API_URL}/admin/logout`, {}, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
            console.log('Déconnexion côté serveur réussie');
        } catch (error) {
            console.error('Erreur lors de la déconnexion côté serveur:', error);
        }
    }

    localStorage.removeItem('admin');
    localStorage.removeItem('token');

    window.location.href = '/admin/login';

    console.log('Déconnexion locale réussie');
};

export const isAuthenticated = () => {
    const token = getAdminToken(); // Correction: Utiliser getAdminToken au lieu de getToken
    return !!token;
};

export const forceLogout = () => {
    localStorage.removeItem('admin');
    localStorage.removeItem('token');
    window.location.href = '/admin/login';
};

export const getDashboardData = async () => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Utilisateur non authentifié');
    }

    try {
        const response = await axios.get(`${API_URL}/admin/dashboard`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.error || 'Erreur lors de la récupération des données');
    }
};

// Nouvelles fonctions ajoutées

export const getAdminMentions = async () => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/admin/mentions`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des mentions';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const getAdminNiveaux = async (mentionId) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/admin/mentions/${mentionId}/niveaux`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des niveaux';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const getAdminCours = async (mentionId, niveauId, semestreId) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des cours';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const getAdminCoursDetails = async (mentionId, niveauId, semestreId, coursId) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours/${coursId}`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des détails du cours';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const addAdminSupport = async (ecId, titre, type, fichier, mimeType, url, isPublic, onProgress) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    const typeMapping = {
        document: 'FICHIER',
        video: 'VIDEO',
        audio: 'AUDIO',
        lien: 'LIEN'
    };
    const backendType = typeMapping[type] || type;

    try {
        const formData = new FormData();
        formData.append('ec_id', ecId);
        formData.append('titre', titre);
        formData.append('type', backendType);
        if (fichier) {
            formData.append('fichier', fichier);
        }
        if (url) {
            formData.append('url', url);
        }
        if (mimeType) {
            formData.append('mimeType', mimeType);
        }
        formData.append('isPublic', isPublic ? '1' : '0');

        const response = await axios.post(`${API_URL}/admin/supports`, formData, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: (progressEvent) => {
                if (onProgress) onProgress(progressEvent);
            }
        });

        if (response.data.support) {
            response.data.support.type = type;
        }

        return response.data;
    } catch (error) {
        let errorMessage = "Erreur lors de l'ajout du support";
        if (error.response) {
            errorMessage = error.response.data?.message || errorMessage;
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const deleteAdminSupport = async (supportId) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.delete(`${API_URL}/admin/supports/${supportId}`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la suppression du support';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const updateEcDescription = async (ecId, description) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.put(
            `${API_URL}/admin/ecs/${ecId}/description`,
            { description },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la mise à jour de la description';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const deleteAdminCommentaire = async (commentaireId) => {
    const token = getAdminToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.delete(`${API_URL}/admin/commentaires/${commentaireId}`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la suppression du commentaire';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};