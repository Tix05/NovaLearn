import axios from './apiConfig';

const API_URL = 'http://localhost:8000/api';

export const adminLogin = async (email, password) => {
    try {
        const response = await axios.post(`${API_URL}/admin/login`, {
            email,
            password
        });

        if (response.data.token) {
            localStorage.setItem('admin', JSON.stringify(response.data));
        }

        return response.data;
    } catch (error) {
        throw new Error(error.response?.data?.message || 'Erreur lors de la connexion');
    }
};

export const getCurrentAdmin = () => {
    const admin = localStorage.getItem('admin');
    return admin ? JSON.parse(admin) : null;
};

export const getAdminToken = () => {
    const admin = getCurrentAdmin();
    return admin ? admin.token : null;
};

export const adminLogout = async () => {
    const token = getAdminToken();

    if (token) {
        try {
            await axios.post(`${API_URL}/admin/logout`, {}, {
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

    localStorage.removeItem('admin');
    localStorage.removeItem('token');

    window.location.href = '/admin/login';

    console.log('Déconnexion locale réussie');
};

export const isAuthenticated = () => {
    const token = getToken();
    return !!token;
};

export const forceLogout = () => {
    localStorage.removeItem('admin');
    localStorage.removeItem('token');
    window.location.href = '/admin/login';
};