import axios from './apiConfig';

const API_URL = 'http://localhost:8000/api';

export const login = async (email, password) => {
    try {
        const response = await axios.post(`${API_URL}/login`, {
            email,
            password
        }, {
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.data.token) {
            const roles = Array.isArray(response.data.roles) ? response.data.roles : ['ROLE_USER'];

            localStorage.setItem('user', JSON.stringify({
                ...response.data,
                roles
            }));

            return {
                ...response.data,
                roles
            };
        }

        throw new Error('Réponse inattendue du serveur');
    } catch (error) {
        let errorMessage = 'Erreur de connexion';

        if (error.response) {
            if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            } else if (error.response.status === 401) {
                errorMessage = 'Email ou mot de passe incorrect';
            } else if (error.response.status === 403) {
                errorMessage = "Vous n'avez pas accès à cette ressource";
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }

        throw new Error(errorMessage);
    }
};

export const logout = () => {
    localStorage.removeItem('user');
};

export const getCurrentUser = () => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
};

export const getToken = () => {
    const user = getCurrentUser();
    return user ? user.token : null;
};

export const getStudentMentions = async () => {
    try {
        const response = await axios.get(`${API_URL}/student/mentions`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching student mentions:", error);
        throw new Error('Erreur lors de la récupération des mentions');
    }
};