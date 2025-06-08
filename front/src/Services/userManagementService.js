import axios from './apiConfig';
import { getAdminToken } from './adminAuthService';

const API_URL = 'http://localhost:8000/api';

const getAuthHeaders = () => ({
    Authorization: `Bearer ${getAdminToken()}`,
    Accept: 'application/ld+json',
});

export const getUsers = async () => {
    try {
        const response = await axios.get(`${API_URL}/users`, {
            headers: getAuthHeaders(),
        });
        return response.data['hydra:member'].map(item => ({
            id: item.id, // User ID
            etudiantId: item.etudiantId || null, // Etudiant ID
            profId: item.profId || null,
            type: item.type,
            nom: item.nom,
            prenom: item.prenom,
            email: item.email,
            telephone: item.telephone,
            avatar: item.avatar,
            matricule: item.matricule || null,
            mention: item.mention?.name || null,
            mentionId: item.mention?.id || null,
            parcours: item.parcours?.name || null,
            parcoursId: item.parcours?.id || null,
            niveau: item.niveau?.name || null,
            niveauId: item.niveau?.id || null,
            status: item.status,
            province: item.province?.region || null,
            provinceId: item.province?.id || null,
            ville: item.ville || null,
            typePaiement: item.type_payement || null,
            referencePaiement: item.reference || null,
            year: item.year?.year || null,
            yearId: item.year?.id || null
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des utilisateurs:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des utilisateurs');
    }
};

export const createEtudiant = async (formData) => {
    try {
        const response = await axios.post(`${API_URL}/etudiants`, formData, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });
        return {
            id: response.data.id, // User ID
            etudiantId: response.data.etudiantId,
            type: response.data.type,
            nom: response.data.nom,
            prenom: response.data.prenom,
            email: response.data.email,
            telephone: response.data.telephone,
            avatar: response.data.avatar,
            matricule: response.data.matricule,
            mention: response.data.mention?.name,
            mentionId: response.data.mention?.id,
            parcours: response.data.parcours?.name,
            parcoursId: response.data.parcours?.id,
            niveau: response.data.niveau?.name,
            niveauId: response.data.niveau?.id,
            status: response.data.status,
            province: response.data.province?.region,
            provinceId: response.data.province?.id,
            ville: response.data.ville,
            typePaiement: response.data.type_payement,
            referencePaiement: response.data.reference,
            year: response.data.year?.year,
            yearId: response.data.year?.id
        };
    } catch (error) {
        console.error('Erreur lors de la création de l\'étudiant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'étudiant');
    }
};

export const createProf = async (formData) => {
    try {
        const response = await axios.post(`${API_URL}/profs`, formData, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });
        return {
            id: response.data.id, // User ID
            profId: response.data.profId,
            type: response.data.type,
            nom: response.data.nom,
            prenom: response.data.prenom,
            email: response.data.email,
            telephone: response.data.telephone,
            avatar: response.data.avatar,
            status: response.data.status,
            province: response.data.province?.region,
            provinceId: response.data.province?.id,
            ville: response.data.ville
        };
    } catch (error) {
        console.error('Erreur lors de la création de l\'enseignant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'enseignant');
    }
};

export const createAdmin = async (formData) => {
    try {
        const response = await axios.post(`${API_URL}/admins`, formData, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });
        return {
            id: response.data.id,
            type: response.data.type,
            nom: response.data.nom,
            prenom: response.data.prenom,
            email: response.data.email,
            telephone: response.data.telephone,
            avatar: response.data.avatar,
            status: response.data.status,
            province: response.data.province?.region,
            provinceId: response.data.province?.id,
            ville: response.data.ville
        };
    } catch (error) {
        console.error('Erreur lors de la création de l\'administrateur:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'administrateur');
    }
};

export const updateEtudiant = async (id, data) => {
    try {
        const response = await axios.patch(`${API_URL}/etudiants/${id}`, data, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        // Fallback if updatedFields is missing
        const updatedFields = response.data.updatedFields || {};

        return {
            id: response.data.id, // User ID
            etudiantId: response.data.etudiantId,
            type: 'etudiant',
            nom: updatedFields.nom || data.nom,
            prenom: updatedFields.prenom || data.prenom,
            email: updatedFields.email || data.email,
            telephone: updatedFields.telephone || data.telephone || 'N/A',
            province: updatedFields.province ? updatedFields.province.region : data.province || 'N/A',
            provinceId: updatedFields.province?.id || data.province,
            ville: updatedFields.ville || data.ville || 'N/A',
            status: updatedFields.status ?? data.status,
            matricule: updatedFields.matricule || data.matricule,
            mention: updatedFields.mention?.name || data.mention,
            mentionId: updatedFields.mention?.id || data.mention,
            parcours: updatedFields.parcours?.name || data.parcours,
            parcoursId: updatedFields.parcours?.id || data.parcours,
            niveau: updatedFields.niveau?.name || data.niveau,
            niveauId: updatedFields.niveau?.id || data.niveau,
            type_payement: updatedFields.type_payement || data.type_payement || 'N/A',
            reference: updatedFields.reference || data.reference || 'N/A',
            year: updatedFields.year?.year || data.year,
            yearId: updatedFields.year?.id || data.year
        };
    } catch (error) {
        console.error('Erreur lors de la modification de l\'étudiant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la modification de l\'étudiant');
    }
};

export const updateProf = async (id, data) => {
    try {
        const response = await axios.patch(`${API_URL}/profs/${id}`, data, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });
        return {
            id: response.data.id, // User ID
            profId: response.data.profId,
            ...response.data.updatedFields,
            type: 'prof',
            provinceId: response.data.updatedFields.province?.id
        };
    } catch (error) {
        console.error('Erreur lors de la modification de l\'enseignant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la modification de l\'enseignant');
    }
};

export const updateAdmin = async (id, data) => {
    try {
        const response = await axios.patch(`${API_URL}/admins/${id}`, data, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });
        return {
            id: response.data.id,
            ...response.data.updatedFields,
            type: 'admin',
            provinceId: response.data.updatedFields.province?.id
        };
    } catch (error) {
        console.error('Erreur lors de la modification de l\'administrateur:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la modification de l\'administrateur');
    }
};

export const uploadUserAvatar = async (id, file) => {
    try {
        const formData = new FormData();
        formData.append('avatarFile', file);
        const response = await axios.post(`${API_URL}/users/${id}/upload`, formData, {
            headers: {
                Authorization: `Bearer ${getAdminToken()}`,
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });
        return response.data;
    } catch (error) {
        console.error('Erreur lors de l\'upload de l\'avatar:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de l\'upload de l\'avatar');
    }
};

export const deleteEtudiant = async (etudiantId) => {
    try {
        await axios.delete(`${API_URL}/etudiants/${etudiantId}`, {
            headers: getAuthHeaders(),
        });
        return true;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'étudiant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'étudiant');
    }
};

export const deleteProf = async (profId) => {
    try {
        await axios.delete(`${API_URL}/profs/${profId}`, {
            headers: getAuthHeaders(),
        });
        return true;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'enseignant:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'enseignant');
    }
};

export const deleteAdmin = async (id) => {
    try {
        await axios.delete(`${API_URL}/admins/${id}`, {
            headers: getAuthHeaders(),
        });
        return true;
    } catch (error) {
        console.error('Erreur lors de la suppression de l\'administrateur:', error.response?.data || error);
        throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'administrateur');
    }
};

export const getMentions = async () => {
    try {
        const response = await axios.get(`${API_URL}/mentions`, {
            headers: getAuthHeaders(),
        });
        return response.data['hydra:member'].map(item => ({
            label: item.name,
            value: item.id
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des mentions:', error.response?.data || error);
        return [];
    }
};

export const getParcours = async (mentionId, niveauId) => {
    try {
        const response = await axios.get(`${API_URL}/admin/parcours`, {
            headers: getAuthHeaders(),
            params: {
                mention: mentionId,
                niveau: niveauId
            }
        });
        return response.data['hydra:member'].map(item => ({
            label: item.name,
            value: item.id
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des parcours:', error.response?.data || error);
        return [];
    }
};

export const getNiveaux = async () => {
    try {
        const response = await axios.get(`${API_URL}/niveaux`, {
            headers: getAuthHeaders(),
        });
        return response.data['hydra:member'].map(item => ({
            label: item.nom,
            value: item.id
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des niveaux:', error.response?.data || error);
        return [];
    }
};

export const getProvinces = async () => {
    try {
        const response = await axios.get(`${API_URL}/provinces`, {
            headers: getAuthHeaders(),
        });
        return response.data['hydra:member'].map(item => ({
            label: item.region,
            value: item.id
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des provinces:', error.response?.data || error);
        return [];
    }
};

export const getYears = async () => {
    try {
        const response = await axios.get(`${API_URL}/years`, {
            headers: getAuthHeaders(),
        });
        return response.data['hydra:member'].map(item => ({
            label: item.year,
            value: item.id
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des années:', error.response?.data || error);
        return [];
    }
};