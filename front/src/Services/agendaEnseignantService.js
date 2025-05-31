import axios from './apiConfig';
import { getTeacherToken } from './teacherAuthService';

const API_URL = 'http://localhost:8000/api';

const AgendaEnseignantService = {
    getTeacherAgenda: async () => {
        try {
            const response = await axios.get(`${API_URL}/agenda/teacher`, {
                headers: {
                    'Authorization': `Bearer ${getTeacherToken()}`,
                    'Accept': 'application/json'
                }
            });
            return response.data;
        } catch (error) {
            console.error('Error fetching teacher agenda:', error);
            throw error;
        }
    }
};

export default AgendaEnseignantService;