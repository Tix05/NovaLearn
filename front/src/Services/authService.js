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

export const logout = async () => {
    const token = getToken();

    if (token) {
        try {
            await axios.post(`${API_URL}/logout`, {}, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
            console.log('Déconnexion côté serveur réussie');
        } catch (error) {
            console.error('Erreur lors de la déconnexion côté serveur:', error);
        }
    }

    localStorage.removeItem('user');
    localStorage.removeItem('token');

    window.location.href = '/etudiant/login-etudiant';

    console.log('Déconnexion locale réussie');
};

export const isAuthenticated = () => {
    const token = getToken();
    return !!token;
};

export const forceLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    window.location.href = '/etudiant/login-etudiant';
};

export const getStudentCoursDetails = async (mentionId, semestreId, coursId) => {
    const token = getToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(
            `${API_URL}/student/mentions/${mentionId}/semestres/${semestreId}/cours/${coursId}`,
            {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des détails du cours';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
                forceLogout();
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const addComment = async (coursId, contenu, parentId = null) => {
    const token = getToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.post(
            `${API_URL}/student/commentaires`,
            {
                coursId,
                contenu,
                parentId
            },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de l\'ajout du commentaire';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
                forceLogout();
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const updateComment = async (commentId, contenu) => {
    const token = getToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.put(
            `${API_URL}/student/commentaires/${commentId}`,
            { contenu },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la mise à jour du commentaire';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
                forceLogout();
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const deleteComment = async (commentId) => {
    const token = getToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.delete(
            `${API_URL}/student/commentaires/${commentId}`,
            {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la suppression du commentaire';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
                forceLogout();
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};
