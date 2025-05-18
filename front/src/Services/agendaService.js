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

        return {
            cours: response.data.cours,
            examens: response.data.examens.map(exam => ({
                ...exam,
                hasLink: !!exam.url,
                examenId: exam.examenId || exam.id
            })),
            evenements: response.data.evenements
        };
    } catch (error) {
        console.error("Error fetching student agenda:", error);
        throw new Error('Erreur lors de la récupération de l\'agenda');
    }
};

export const fetchExamQuestions = async (examId) => {
    try {
        const response = await axios.get(`${API_URL}/examen/${examId}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching exam questions:", error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des questions de l\'examen');
    }
};