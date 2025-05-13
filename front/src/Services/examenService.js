import axios from 'axios';
import { getTeacherToken } from './teacherAuthService';

const API_URL = 'http://localhost:8000/api';

export const createExamen = async (ecId, titre, description, type, instructions, file, duration) => {
    const formData = new FormData();
    formData.append('ec_id', ecId);
    formData.append('titre', titre);
    formData.append('description', description);
    formData.append('type', type);
    formData.append('instructions', instructions);
    formData.append('duree', duration);
    if (file) {
        formData.append('file', file);
    }

    try {
        const headers = {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${getTeacherToken()}`,
        };

        const response = await axios.post(`${API_URL}/examen/create`, formData, { headers });
        return response.data;
    } catch (error) {
        const message = error.response?.data?.message || error.response?.data?.detail || error.message || 'Erreur lors de la création de l\'examen';
        throw new Error(message);
    }
};

export const submitExamenToAdmin = async (ecId, titre, description, type, instructions, file, tempFile, questions, duration) => {
    const formData = new FormData();
    formData.append('ec_id', ecId);
    formData.append('titre', titre);
    formData.append('description', description);
    formData.append('type', type);
    formData.append('instructions', instructions);
    formData.append('temp_file', tempFile || '');
    formData.append('questions', JSON.stringify(questions));
    formData.append('duree', duration);
    if (file) {
        formData.append('file', file);
    }

    try {
        const headers = {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${getTeacherToken()}`,
        };

        const response = await axios.post(`${API_URL}/examen/submit-to-admin`, formData, { headers });
        return response.data;
    } catch (error) {
        const message = error.response?.data?.message || error.response?.data?.detail || error.message || 'Erreur lors de la soumission de l\'examen';
        throw new Error(message);
    }
};

export const getEcSupports = async (ecId) => {
    try {
        const response = await axios.get(`${API_URL}/teacher/ec/${ecId}/supports`, {
            headers: {
                'Authorization': `Bearer ${getTeacherToken()}`,
            },
        });
        return response.data;
    } catch (error) {
        const message = error.response?.data?.message || error.response?.data?.detail || error.message || 'Erreur lors de la récupération des supports';
        throw new Error(message);
    }
};