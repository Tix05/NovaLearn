import axios from 'axios';

const API_URL = 'http://localhost:8000/api/examen';

const getAuthHeaders = () => {
    const admin = JSON.parse(localStorage.getItem('admin') || '{}');
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
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la récupération des examens des enseignants:', error);
        throw error.response?.data?.message || 'Erreur serveur';
    }
};

export const deleteTeacherExam = async (id) => {
    try {
        const response = await axios.delete(`${API_URL}/${id}`, getAuthHeaders());
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'examen:', error);
        throw error.response?.data?.message || 'Erreur serveur';
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
        const response = await axios.post(`${API_URL}/${id}/publish`, dates, getAuthHeaders());
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la publication de l\'examen:', error);
        throw error.response?.data?.message || error.message || 'Erreur serveur';
    }
};

export const previewTeacherExam = async (id, dates) => {
    try {
        if (!dates.date_debut || !dates.date_fin) {
            throw new Error('Les dates de début et de fin sont requises');
        }
        if (new Date(dates.date_debut) >= new Date(dates.date_fin)) {
            throw new Error('La date de fin doit être postérieure à la date de début');
        }
        const response = await axios.post(
            `${API_URL}/${id}/publish`,
            { ...dates, preview: true },
            getAuthHeaders()
        );
        return response.data;
    } catch (error) {
        console.error('Erreur lors de la prévisualisation de l\'examen:', error);
        throw error.response?.data?.message || error.message || 'Erreur serveur';
    }
};