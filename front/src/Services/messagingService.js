import axios from 'axios';
import { getToken } from './authService';

const API_URL = 'http://localhost:8000/api/messaging';

export const getConversations = async () => {
    try {
        const token = getToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.get(`${API_URL}/conversations`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        console.error('Error fetching conversations:', error);
        throw error;
    }
};

export const getMessages = async (conversationId) => {
    try {
        const token = getToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.get(`${API_URL}/messages/${conversationId}`, {
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        console.error('Error fetching messages:', error);
        throw error;
    }
};

export const sendMessage = async (conversationId, recipientId, parcoursId, content) => {
    try {
        const token = getToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.post(
            `${API_URL}/send`,
            {
                conversationId,
                recipientId,
                parcoursId,
                contenu: content,
            },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            }
        );
        return response.data;
    } catch (error) {
        console.error('Error sending message:', error);
        throw error;
    }
};

export const markMessageAsRead = async (messageId) => {
    try {
        const token = getToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.post(
            `${API_URL}/mark-read/${messageId}`,
            {},
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            }
        );
        return response.data;
    } catch (error) {
        console.error('Error marking message as read:', error);
        throw error;
    }
};