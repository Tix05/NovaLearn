import axios from './apiConfig';
import { getToken } from './authService';

const API_URL = 'http://localhost:8000/api';
const BASE_URL = 'http://localhost:8000';

export const getBibliothequeItems = async () => {
    try {
        const response = await axios.get(`${API_URL}/bibliotheques`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/ld+json'
            }
        });

        const items = response.data['hydra:member'] || [];

        return items.map(item => ({
            id: item['@id'].split('/').pop(),
            titre: item.titre || 'N/A',
            type: item.type || 'N/A',
            fichier: item.fichier ? item.fichier : null,
            mentionName: item.mentionName || 'N/A',
            niveauNom: item.niveauNom || 'N/A',
            ecName: item.ecName || 'N/A'
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des éléments de la bibliothèque:', error);
        return [];
    }
};