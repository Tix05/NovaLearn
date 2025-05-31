import axios from './apiConfig';
import { getTeacherToken } from './teacherAuthService';

const API_URL = 'http://localhost:8000/api';

export const getBibliothequeItems = async () => {
    try {
        const response = await axios.get(`${API_URL}/bibliotheques`, {
            headers: {
                Authorization: `Bearer ${getTeacherToken()}`,
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
                    Authorization: `Bearer ${getTeacherToken()}`,
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
                Authorization: `Bearer ${getTeacherToken()}`,
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
                Authorization: `Bearer ${getTeacherToken()}`,
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
                Authorization: `Bearer ${getTeacherToken()}`,
                Accept: 'application/ld+json',
            },
        });
        return true;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'élément de la bibliothèque:', error);
        throw error;
    }
};

export const getTeacherMentions = async () => {
    try {
        const response = await axios.get(`${API_URL}/teacher/mentions`, {
            headers: {
                Authorization: `Bearer ${getTeacherToken()}`,
                Accept: 'application/json',
            },
        });

        const mentions = response.data.map(item => ({
            label: item.nom,
            value: item.id,
            niveaux: item.semestres.map(semestre => ({
                label: semestre.intitule,
                value: semestre.id,
                ues: semestre.ues.map(ue => ({
                    label: ue.nom,
                    value: ue.id,
                    ecs: ue.cours.map(cours => ({
                        label: cours.titre,
                        value: cours.id,
                    })),
                })),
            })),
            parcours: item.parcours.map(parcours => ({
                label: parcours,
                value: parcours,
            })),
        }));

        return mentions;
    } catch (error) {
        console.error('Erreur lors de la récupération des mentions:', error);
        return [];
    }
};