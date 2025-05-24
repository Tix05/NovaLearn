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

export const adminLogout = () => {
    localStorage.removeItem('admin');
};