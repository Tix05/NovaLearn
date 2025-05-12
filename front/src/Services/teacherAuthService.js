import axios from 'axios';

const API_URL = 'http://localhost:8000/api/teacher';

export const teacherLogin = async (email, password) => {
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

            localStorage.setItem('teacher', JSON.stringify({
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
                errorMessage = "Vous n'avez pas accès à l'espace enseignant";
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const teacherLogout = () => {
    localStorage.removeItem('teacher');
};

export const getCurrentTeacher = () => {
    const teacher = localStorage.getItem('teacher');
    return teacher ? JSON.parse(teacher) : null;
};

export const getTeacherToken = () => {
    const teacher = getCurrentTeacher();
    return teacher ? teacher.token : null;
};

export const getTeacherDashboardStats = async () => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/dashboard/stats`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des statistiques';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const getTeacherStudents = async () => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/students`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des étudiants';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const getTeacherMentions = async () => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.get(`${API_URL}/mentions`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la récupération des mentions';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const addTeacherSupport = async (ecId, titre, type, fichier, mimeType, url, isPublic) => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    const typeMapping = {
        document: 'FICHIER',
        video: 'VIDEO',
        audio: 'AUDIO',
        lien: 'LIEN'
    };
    const backendType = typeMapping[type] || type;

    try {
        const formData = new FormData();
        formData.append('ec_id', ecId);
        formData.append('titre', titre);
        formData.append('type', backendType);
        if (fichier) {
            formData.append('fichier', fichier);
        }
        if (url) {
            formData.append('url', url);
        }
        if (mimeType) {
            formData.append('mimeType', mimeType);
        }
        formData.append('isPublic', isPublic ? '1' : '0');

        const response = await axios.post(`${API_URL}/supports`, formData, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        });

        // Remapper le type dans la réponse pour correspondre au frontend
        if (response.data.support) {
            response.data.support.type = type; // Restaurer le type frontend
        }

        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de l\'ajout du support';
        if (error.response) {
            errorMessage = error.response.data?.message || errorMessage;
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const deleteTeacherSupport = async (supportId) => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.delete(`${API_URL}/supports/${supportId}`, {
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la suppression du support';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};

export const updateEcDescription = async (ecId, description) => {
    const token = getTeacherToken();
    if (!token) {
        throw new Error('Aucun token trouvé');
    }

    try {
        const response = await axios.put(
            `${API_URL}/ecs/${ecId}/description`,
            { description },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    } catch (error) {
        let errorMessage = 'Erreur lors de la mise à jour de la description';
        if (error.response) {
            if (error.response.status === 401) {
                errorMessage = 'Session expirée ou non autorisée';
            } else if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
        } else if (error.request) {
            errorMessage = 'Le serveur ne répond pas';
        }
        throw new Error(errorMessage);
    }
};