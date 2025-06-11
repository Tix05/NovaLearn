import axios from './apiConfig';

const API_URL = 'http://localhost:8000/api/examen';

const getAuthHeaders = () => {
    const admin = JSON.parse(sessionStorage.getItem('admin') || '{}');
    const token = admin?.token;
    if (!token) {
        throw new Error('Utilisateur non authentifié');
    }
    return {
        headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
    };
};

export const getTeacherExams = async () => {
    try {
        const response = await axios.get(`${API_URL}/teacher`, getAuthHeaders());
        if (!Array.isArray(response.data)) {
            throw new Error('Réponse inattendue du serveur');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération des examens des enseignants:', error);
        throw error.response?.data?.message || 'Erreur serveur ou réseau';
    }
};

export const deleteTeacherExam = async (id) => {
    try {
        const response = await axios.delete(`${API_URL}/${id}`, getAuthHeaders());
        if (!response.data.message) {
            throw new Error('Réponse inattendue du serveur');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'examen:', error);
        throw error.response?.data?.message || 'Erreur serveur ou réseau';
    }
};

export const publishTeacherExam = async (id, dates) => {
    try {
        if (!dates.date_debut || !dates.date_fin) {
            throw new Error('Les dates de début et de fin sont requises');
        }
        if (new Date(dates.date_debut) >= new Date(dates.date_fin)) {
            throw new Error('La date de fin doit être postérieure à la date de début');
        }
        const response = await axios.post(
            `${API_URL}/${id}/publish`,
            {
                date_debut: dates.date_debut,
                date_fin: dates.date_fin,
            },
            getAuthHeaders()
        );
        console.log('Réponse de publishTeacherExam:', response.data);
        if (!response.data.message) {
            throw new Error('Réponse inattendue du serveur');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la publication de l\'examen:', error);
        throw error.response?.data?.message || error.message || 'Erreur serveur ou réseau';
    }
};

export async function previewTeacherExam(examenId, dates) {
    try {
        if (!dates.date_debut || !dates.date_fin) {
            throw new Error('Les dates de début et de fin sont requises');
        }
        if (new Date(dates.date_debut) >= new Date(dates.date_fin)) {
            throw new Error('La date de fin doit être postérieure à la date de début');
        }
        const response = await axios.post(
            `${API_URL}/${examenId}/publish`,
            {
                date_debut: dates.date_debut,
                date_fin: dates.date_fin,
                preview: true,
            },
            getAuthHeaders()
        );
        console.log('Réponse de l\'API pour previewTeacherExam:', response.data);
        if (!response.data.data) {
            throw new Error('Données de prévisualisation manquantes');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la prévisualisation de l\'examen:', error, error.response?.data);
        throw error.response?.data?.message || error.message || 'Erreur serveur ou réseau';
    }
};

export const getStudentExams = async () => {
    try {
        const response = await axios.get(`${API_URL}/student`, getAuthHeaders());
        if (!Array.isArray(response.data)) {
            throw new Error('Réponse inattendue du serveur');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération des examens des étudiants:', error);
        throw error.response?.data?.message || 'Erreur serveur ou réseau';
    }
};

export const deleteCorrection = async (id) => {
    try {
        const response = await axios.delete(`${API_URL}/correction/${id}`, getAuthHeaders());
        if (!response.data.message) {
            throw new Error('Réponse inattendue du serveur');
        }
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la suppression de la correction:', error);
        throw error.response?.data?.message || 'Erreur serveur ou réseau';
    }
};
