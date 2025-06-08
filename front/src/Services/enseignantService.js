// enseignantService.js
import axios from './apiConfig';
import { getCurrentUser } from './authService';

const API_URL = 'http://localhost:8000/api';

export const getEnseignantsByEtudiant = async () => {
    try {
        const user = getCurrentUser();
        if (!user || !user.token) {
            throw new Error('Utilisateur non authentifié');
        }

        const response = await axios.get(`${API_URL}/ecs/profs_by_etudiant`, {
            headers: {
                Authorization: `Bearer ${user.token}`,
                'Content-Type': 'application/json'
            }
        });

        return response.data.map(prof => ({
            id: prof.id,
            nom: prof.user.nomComplet,
            ec: prof.ecs.join(' - '),
            niveau: prof.niveau || 'Niveau non spécifié',
            profil: prof.user.avatar
        }));
    } catch (error) {
        console.error('Erreur lors de la récupération des enseignants:', error);
        throw error;
    }
};

export const getNotesByEtudiant = async () => {
    try {
        const user = getCurrentUser();
        if (!user || !user.token) {
            throw new Error('Utilisateur non authentifié');
        }

        // Récupérer l'ID de l'étudiant connecté
        const etudiantResponse = await axios.get(`${API_URL}/etudiants`, {
            headers: {
                Authorization: `Bearer ${user.token}`,
                'Content-Type': 'application/json'
            },
            params: {
                'user.id': user.id
            }
        });

        const etudiant = etudiantResponse.data['hydra:member'][0];
        if (!etudiant) {
            throw new Error('Étudiant non trouvé');
        }

        // Récupérer les corrections d'examen pour cet étudiant
        const correctionsResponse = await axios.get(`${API_URL}/correction_examens`, {
            headers: {
                Authorization: `Bearer ${user.token}`,
                'Content-Type': 'application/json'
            },
            params: {
                'etudiant.id': etudiant.id
            }
        });

        const corrections = correctionsResponse.data['hydra:member'];

        // Transformer les corrections en données pour le graphique
        const notes = corrections.map(correction => ({
            ec: correction.nomEc || 'EC inconnu', // Utiliser nomEc directement
            note: correction.noteTotale // Garder la note sur 20
        }));

        return notes;
    } catch (error) {
        console.error('Erreur lors de la récupération des notes:', error);
        throw error;
    }
};