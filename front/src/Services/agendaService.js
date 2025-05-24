import axios from './apiConfig';
import { getToken } from './authService';

const API_URL = 'http://localhost:8000/api';

export const getStudentAgenda = async () => {
    try {
        const response = await axios.get(`${API_URL}/agenda/student`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 10000 // Timeout de 10 secondes
        });

        const data = response.data || {};
        if (!data.cours || !Array.isArray(data.cours)) {
            console.warn('Cours data is missing or not an array:', data.cours);
            data.cours = [];
        }
        if (!data.examens || !Array.isArray(data.examens)) {
            console.warn('Examens data is missing or not an array:', data.examens);
            data.examens = [];
        }
        if (!data.evenements || !Array.isArray(data.evenements)) {
            console.warn('Evenements data is missing or not an array:', data.evenements);
            data.evenements = [];
        }

        return {
            cours: data.cours,
            examens: data.examens.map(exam => ({
                ...exam,
                examenId: exam.examenId || exam.id
            })),
            evenements: data.evenements
        };
    } catch (error) {
        console.error('Error fetching student agenda:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw new Error(error.response?.data?.message || 'Erreur lors de la récupération de l\'agenda');
    }
};

export const fetchExamQuestions = async (examId) => {
    try {
        const response = await axios.get(`${API_URL}/examen/${examId}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 10000
        });

        let tempsRestant = response.data.duree || 3600;
        if (response.data.statut === 'EN_COURS') {
            try {
                const timeData = await getExamTime(examId);
                tempsRestant = timeData.temps_restant !== undefined && timeData.temps_restant >= 0
                    ? timeData.temps_restant
                    : response.data.temps_restant || response.data.duree || 3600;
            } catch (error) {
                console.error('Error fetching exam time:', error);
                const cached = localStorage.getItem(`examTime_${examId}`);
                if (cached) {
                    const { time, lastSync } = JSON.parse(cached);
                    const elapsed = Math.floor((Date.now() - lastSync) / 1000);
                    tempsRestant = Math.max(0, time - elapsed);
                }
            }
        }

        return {
            questions: response.data.questions || [],
            duree: response.data.duree || 3600,
            statut: response.data.statut || 'DISPONIBLE',
            temps_restant: tempsRestant,
            reponses: response.data.reponses || {}
        };
    } catch (error) {
        console.error('Error fetching exam questions:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        return {
            questions: [],
            duree: 3600,
            statut: 'DISPONIBLE',
            temps_restant: 3600,
            reponses: {}
        };
    }
};

export const getExamStatus = async (examId) => {
    try {
        const response = await axios.get(`${API_URL}/examen/${examId}`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 10000
        });
        return {
            statut: response.data.statut || 'DISPONIBLE',
            temps_restant: response.data.temps_restant || 3600,
            reponses: response.data.reponses || {}
        };
    } catch (error) {
        console.error('Error fetching exam status:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        return { statut: 'DISPONIBLE', temps_restant: 3600, reponses: {} };
    }
};

export const getExamTime = async (examId) => {
    try {
        const response = await axios.get(`${API_URL}/examen/${examId}/get-time`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 5000
        });

        // Si le temps est écoulé mais que le statut est toujours EN_COURS, le serveur devrait automatiquement soumettre
        if (response.data.temps_restant <= 0 && response.data.statut === 'EN_COURS') {
            console.warn('Temps écoulé mais statut toujours EN_COURS - le serveur devrait soumettre automatiquement');
            // Le serveur devrait retourner SOUMIS dans ce cas, mais nous forçons une nouvelle vérification
            const statusResponse = await axios.get(`${API_URL}/examen/${examId}`, {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json'
                },
                timeout: 5000
            });
            return statusResponse.data;
        }

        return response.data;
    } catch (error) {
        console.error('Error getting exam time:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw error;
    }
};

export const startExam = async (examId) => {
    try {
        const response = await axios.post(`${API_URL}/examen/${examId}/start`, {}, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 10000
        });
        return response.data;
    } catch (error) {
        console.error('Error starting exam:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw new Error(error.response?.data?.message || 'Erreur lors du démarrage de l\'examen');
    }
};

export const submitExam = async (examId, answers) => {
    try {
        const response = await axios.post(
            `${API_URL}/examen/${examId}/submit`,
            { answers },
            {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                timeout: 15000
            }
        );
        return response.data;
    } catch (error) {
        console.error('Error submitting exam:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw new Error(error.response?.data?.message || 'Erreur lors de la soumission de l\'examen');
    }
};

export const abandonExam = async (examId) => {
    try {
        const response = await axios.post(`${API_URL}/examen/${examId}/abandon`, {}, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            },
            timeout: 10000
        });
        return response.data;
    } catch (error) {
        console.error('Error abandoning exam:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw new Error(error.response?.data?.message || 'Erreur lors de l\'abandon de l\'examen');
    }
};

export const saveExamProgress = async (examId, answers, tempsRestant) => {
    try {
        const response = await axios.post(
            `${API_URL}/examen/${examId}/save-progress`,
            {
                answers,
                temps_restant: tempsRestant
            },
            {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                timeout: 10000
            }
        );

        if (response.data.temps_restant !== undefined) {
            localStorage.setItem(`examTimeLeft_${examId}`, response.data.temps_restant);
            localStorage.setItem(`examLastSave_${examId}`, new Date().toISOString());
        }

        return response.data;
    } catch (error) {
        console.error('Erreur sauvegarde progression:', error);
        if (error.response?.status === 401) {
            throw new Error('Session expirée. Veuillez vous reconnecter.');
        }
        throw new Error(error.response?.data?.message || 'Erreur lors de la sauvegarde de la progression');
    }
};
