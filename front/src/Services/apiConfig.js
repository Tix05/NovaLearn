import axios from 'axios';

const getCurrentUserData = () => {
    const admin = localStorage.getItem('admin');
    if (admin) return { type: 'admin', data: JSON.parse(admin), storageKey: 'admin' };

    const user = localStorage.getItem('user');
    if (user) return { type: 'user', data: JSON.parse(user), storageKey: 'user' };

    const teacher = localStorage.getItem('teacher');
    if (teacher) return { type: 'teacher', data: JSON.parse(teacher), storageKey: 'teacher' };

    return null;
};

axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            const userData = getCurrentUserData();

            if (userData) {
                try {
                    // Appel logout vers l'API
                    const token = userData.data.token;
                    if (token) {
                        await axios.post('http://localhost:8000/api/logout', {}, {
                            headers: {
                                Authorization: `Bearer ${token}`,
                                'Content-Type': 'application/json',
                            },
                        });
                        console.log('Déconnexion côté serveur réussie suite à 401');
                    }
                } catch (logoutError) {
                    console.error('Erreur lors de la déconnexion côté serveur:', logoutError);
                }

                // Suppression du localStorage
                localStorage.removeItem(userData.storageKey);

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

    if (userData) {
        const token = userData.data.token;

        if (token) {
            try {
                await axios.post('http://localhost:8000/api/logout', {}, {
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

        // Supprimer les données du localStorage
        localStorage.removeItem(userData.storageKey);

        // Rediriger en fonction du type d'utilisateur
        let redirectPath = '/';
        if (userData.type === 'admin') {
            redirectPath = '/admin/login';
        } else if (userData.type === 'teacher') {
            redirectPath = '/enseignant/login-enseignant';
        } else if (userData.type === 'user') {
            redirectPath = '/etudiant/login-etudiant';
        }

        window.location.href = redirectPath;
        console.log('Déconnexion locale réussie');
    } else {
        // Si aucun utilisateur n'est connecté, rediriger vers la page par défaut
        window.location.href = '/';
        console.log('Aucun utilisateur connecté, redirection vers la page d\'accueil');
    }
};

export default axios;