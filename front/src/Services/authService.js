import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

export const login = async (email, password) => {
    try {
        const response = await axios.post(`${API_URL}/login`, {
            email,
            password
        });

        if (response.data.token) {
            localStorage.setItem('user', JSON.stringify(response.data));
        }

        return response.data;
    } catch (error) {
        throw error.response?.data || error.message;
    }
};

export const logout = () => {
    localStorage.removeItem('user');
};

export const getCurrentUser = () => {
    return JSON.parse(localStorage.getItem('user'));
};