// src/services/agendaService.js

import axios from 'axios';
import { getToken } from './authService';

const API_URL = 'http://localhost:8000/api';

export const getStudentAgenda = async () => {
    try {
        const response = await axios.get(`${API_URL}/agenda/student`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching student agenda:", error);
        throw new Error('Erreur lors de la récupération de l\'agenda');
    }
};

export const getCurrentUser = () => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
};