// bibliothequeAdminService.js
import axios from './apiConfig';
import { getAdminToken } from './adminAuthService'; // Assurez-vous d'avoir un service pour récupérer le token admin

const API_URL = 'http://localhost:8000/api';

export const getBibliothequeItems = async () => {
    try {
        const response = await axios.get(`${API_URL}/bibliotheques`, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                Accept: 'application/ld+json',
            },
        });

        const items = response.data['hydra:member'] || [];

        return items.map(item => ({
            id: item['@id'].split('/').pop(),
            titre: item.titre || 'N/A',
            type: item.type || 'N/A',
            fichier: item.fichier ? item.fichier : null,
            mentionName: item.mentionName || 'N/A',
            niveauNom: item.niveauNom || 'N/A',
            ecName: item.ecName || 'N/A',
            parcoursName: item.parcoursName || 'N/A',
            isPublished: item.status || false,
            description: item.description || '',
            source: item['@id'].includes('/bibliotheques/') ? 'bibliotheque' : 'fichier_support',
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des éléments de la bibliothèque:', error);
        return [];
    }
};

export const createBibliothequeItem = async (formData) => {
    try {
        const response = await axios.post(
            `${API_URL}/bibliotheques`,
            formData,
            {
                headers: {
                    Authorization: `Bearer ${getAdminToken()}`,
                    'Content-Type': 'multipart/form-data',
                    Accept: 'application/json',
                },
            }
        );
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la création de l\'élément de bibliothèque:', error);
        throw error;
    }
};

export const updateBibliothequeItem = async (id, data) => {
    try {
        const validTypes = ['administration', 'sujet avec corrigé', 'exercice'];
        if (data.type && !validTypes.includes(data.type)) {
            throw new Error('Type invalide. Les types autorisés sont : administration, sujet avec corrigé, exercice');
        }

        const response = await axios.patch(`${API_URL}/bibliotheques/${id}`, data, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la modification de l\'élément de la bibliothèque:', error);
        throw error;
    }
};

export const uploadBibliothequeFile = async (id, file) => {
    try {
        const formData = new FormData();
        formData.append('file', file);

        const response = await axios.post(`${API_URL}/bibliotheques/${id}/upload`, formData, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        console.error('Erreur lors de l\'upload du fichier:', error);
        throw error;
    }
};

export const deleteBibliothequeItem = async (id) => {
    try {
        await axios.delete(`${API_URL}/bibliotheques/${id}`, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                Accept: 'application/ld+json',
            },
        });
        return true;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'élément de la bibliothèque:', error);
        throw error;
    }
};

export const getAdminMentions = async () => {
    try {
        const response = await axios.get(`${API_URL}/admin/mentions`, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                Accept: 'application/json',
            },
        });

        const mentions = response.data.map(mention => ({
            label: mention.nom,
            value: mention.id,
        }));

        return mentions;
    } catch (error) {
        console.error('Erreur lors de la récupération des mentions:', error);
        return [];
    }
};

export const getAdminNiveaux = async (mentionId) => {
    try {
        const response = await axios.get(`${API_URL}/admin/mentions/${mentionId}/niveaux`, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                Accept: 'application/json',
            },
        });

        const niveaux = response.data.map(niveau => ({
            label: niveau.nom,
            value: niveau.id,
            parcours: niveau.parcours.map(parcours => ({
                label: parcours,
                value: parcours,
            })),
            premierSemestreId: niveau.premierSemestreId,
        }));

        return niveaux;
    } catch (error) {
        console.error('Erreur lors de la récupération des niveaux:', error);
        return [];
    }
};

export const getAdminCours = async (mentionId, niveauId, semestreId) => {
    try {
        const response = await axios.get(`${API_URL}/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours`, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                Accept: 'application/json',
            },
        });

        const ues = response.data.ues.map(ue => ({
            label: ue.nom,
            value: ue.id,
            ecs: ue.cours.map(cours => ({
                label: cours.titre,
                value: cours.id,
            })),
        }));

        return ues;
    } catch (error) {
        console.error('Erreur lors de la récupération des cours:', error);
        return [];
    }
};