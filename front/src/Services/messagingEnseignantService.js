import axios from './apiConfig';
import { getTeacherToken } from './teacherAuthService';

const API_URL = 'http://localhost:8000/api/messaging';

export const getConversations = async () => {
    try {
        const token = getTeacherToken();
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
        const token = getTeacherToken();
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
        const token = getTeacherToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.post(
            `${API_URL}/send`,
            {
                conversationId,
                recipientId,
                parcoursId,
                content
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

export const markMessagesAsRead = async (messageIds) => {
    try {
        const token = await getTeacherToken();
        if (!token) {
            throw new Error('No token found');
        }
        const response = await axios.post(
            `${API_URL}/mark-read`,
            { messageIds },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            }
        );
        return response.data;
    } catch (error) {
        console.error('Error marking messages as read:', error);
        throw error;
    }
};