import axios from 'axios';
import { getCurrentUser } from './authService'; // Modification ici

const API_URL = 'http://localhost:8000/api'; // Remplacez par votre URL réelle

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
            profil: prof.user.avatar || `https://randomuser.me/api/portraits/men/${prof.id}.jpg`
        }));

    } catch (error) {
        console.error('Erreur lors de la récupération des enseignants:', error);
        throw error;
    }
};