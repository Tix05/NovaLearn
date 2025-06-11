import axios from 'axios';

const getCurrentUserData = () => {
    const admin = sessionStorage.getItem('admin');
    if (admin) return { type: 'admin', data: JSON.parse(admin), storageKey: 'admin' };

    const user = sessionStorage.getItem('user');
    if (user) return { type: 'user', data: JSON.parse(user), storageKey: 'user' };

    const teacher = sessionStorage.getItem('teacher');
    if (teacher) return { type: 'teacher', data: JSON.parse(teacher), storageKey: 'teacher' };

    return null;
};

// Fonction pour obtenir l'endpoint de logout selon le type d'utilisateur
const getLogoutEndpoint = (userType) => {
    switch (userType) {
        case 'admin':
            return 'http://localhost:8000/api/admin/logout';
        case 'teacher':
            return 'http://localhost:8000/api/teacher/logout';
        case 'user':
            return 'http://localhost:8000/api/logout';
        default:
            return 'http://localhost:8000/api/logout';
    }
};

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            const userData = getCurrentUserData();
            console.log('Intercepteur 401 déclenché', { userData });

            if (userData) {
                try {
                    const logoutEndpoint = getLogoutEndpoint(userData.type);
                    console.log('Envoi de la requête de déconnexion', {
                        endpoint: logoutEndpoint,
                        email: userData.data.email
                    });
                    await axios.post(logoutEndpoint, { email: userData.data.email }, {
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    });
                    console.log(`Déconnexion côté serveur réussie (${userData.type})`);
                } catch (logoutError) {
                    console.error('Erreur lors de la déconnexion côté serveur:', logoutError.response?.data || logoutError.message);
                }

                // Supprimer les données du sessionStorage
                sessionStorage.removeItem(userData.storageKey);

                // Redirection selon le type
                let redirectPath = '/';
                let errorMessage = 'Session expirée, veuillez vous reconnecter';

                if (userData.type === 'admin') {
                    redirectPath = '/admin/login';
                    errorMessage = 'Session administrateur expirée, veuillez vous reconnecter';
                } else if (userData.type === 'teacher') {
                    redirectPath = '/enseignant/login-enseignant';
                    errorMessage = 'Session enseignant expirée, veuillez vous reconnecter';
                } else if (userData.type === 'user') {
                    redirectPath = '/etudiant/login-etudiant';
                    errorMessage = 'Session étudiant expirée, veuillez vous reconnecter';
                }

                console.log(`Session expirée pour ${userData.type}, redirection vers ${redirectPath}`);
                window.location.href = redirectPath;
                return Promise.reject(new Error(errorMessage));
            }

            // Aucun utilisateur connecté
            window.location.href = '/';
            return Promise.reject(new Error('Session non autorisée, veuillez vous reconnecter'));
        }

        return Promise.reject(error);
    }
);

// Fonction de déconnexion centralisée
export const logout = async () => {
    const userData = getCurrentUserData();
    console.log('Fonction logout appelée', { userData });

    if (userData) {
        try {
            const logoutEndpoint = getLogoutEndpoint(userData.type);
            console.log('Envoi de la requête de déconnexion', {
                endpoint: logoutEndpoint,
                email: userData.data.email
            });
            await axios.post(logoutEndpoint, { email: userData.data.email }, {
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            console.log(`Déconnexion côté serveur réussie (${userData.type})`);
        } catch (error) {
            console.error('Erreur lors de la déconnexion côté serveur:', error.response?.data || error.message);
        }

        // Supprimer les données du sessionStorage
        sessionStorage.removeItem(userData.storageKey);

        // Rediriger en fonction du type d'utilisateur
        let redirectPath = '/';
        if (userData.type === 'admin') {
            redirectPath = '/admin/login';
        } else if (userData.type === 'teacher') {
            redirectPath = '/enseignant/login-enseignant';
        } else if (userData.type === 'user') {
            redirectPath = '/etudiant/login-etudiant';
        }

        console.log(`Déconnexion locale réussie (${userData.type})`);
        window.location.href = redirectPath;
    } else {
        console.log('Aucun utilisateur connecté, redirection vers la page d\'accueil');
        window.location.href = '/';
    }
};

export default axios;