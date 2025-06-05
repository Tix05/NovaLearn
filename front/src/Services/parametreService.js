import axios from './apiConfig';
import { getAdminToken } from './adminAuthService';

const API_BASE_URL = 'http://localhost:8000/api';

// Fonction utilitaire pour ajouter le token d'admin
const getAuthHeaders = () => {
    const token = getAdminToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
};

const parametreService = {
    // Mentions
    getMentions: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/mentions`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des mentions');
        }
    },
    createMention: async (mentionData) => {
        try {
            const formData = new FormData();
            formData.append('name', mentionData.name);
            if (mentionData.iconFile) {
                formData.append('iconFile', mentionData.iconFile);
            }
            const response = await axios.post(`${API_BASE_URL}/mentions`, formData, {
                headers: {
                    ...getAuthHeaders()
                }
            });
            return response.data;
        } catch (error) {
            console.error('Create Mention Error:', error.response?.data);
            throw new Error(error.response?.data?.['hydra:description'] || 'Erreur lors de la création de la mention');
        }
    },
    updateMention: async (id, mentionData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/mentions/${id}`, {
                name: mentionData.name
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour de la mention');
        }
    },
    uploadMentionIcon: async (id, iconFile) => {
        try {
            const formData = new FormData();
            formData.append('iconFile', iconFile);
            const response = await axios.post(`${API_BASE_URL}/mentions/${id}/upload`, formData, {
                headers: {
                    ...getAuthHeaders()
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de l\'upload de l\'icône');
        }
    },
    deleteMention: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/mentions/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de la mention');
        }
    },

    // Niveaux
    getNiveaux: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/niveaux`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des niveaux');
        }
    },
    createNiveau: async (niveauData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/niveaux`, {
                code: niveauData.code,
                nom: niveauData.nom,
                cycle: niveauData.cycle,
                ordre: parseInt(niveauData.ordre)
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création du niveau');
        }
    },
    updateNiveau: async (id, niveauData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/niveaux/${id}`, {
                code: niveauData.code,
                nom: niveauData.nom,
                cycle: niveauData.cycle,
                ordre: parseInt(niveauData.ordre)
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour du niveau');
        }
    },
    deleteNiveau: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/niveaux/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression du niveau');
        }
    },

    // Parcours
    getParcours: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/parcours`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des parcours');
        }
    },
    createParcours: async (parcoursData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/parcours`, {
                name: parcoursData.name,
                full_name: parcoursData.full_name,
                mention: parseInt(parcoursData.mention),
                niveau: parseInt(parcoursData.niveau)
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création du parcours');
        }
    },
    updateParcours: async (id, parcoursData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/parcours/${id}`, {
                name: parcoursData.name,
                full_name: parcoursData.full_name,
                mention: parseInt(parcoursData.mention),
                niveau: parseInt(parcoursData.niveau)
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour du parcours');
        }
    },
    deleteParcours: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/parcours/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression du parcours');
        }
    },

    // Semestres
    getSemestres: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/semestres`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des semestres');
        }
    },
    createSemestre: async (semestreData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/semestres`, {
                name: semestreData.name,
                code: semestreData.code,
                niveau: parseInt(semestreData.niveau)
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création du semestre');
        }
    },
    updateSemestre: async (id, semestreData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/semestres/${id}`, {
                name: semestreData.name,
                code: semestreData.code,
                niveau: parseInt(semestreData.niveau)
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour du semestre');
        }
    },
    deleteSemestre: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/semestres/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression du semestre');
        }
    },

    // Ues
    getUes: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/ues`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des unités d\'enseignement');
        }
    },
    createUe: async (ueData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/ues`, {
                name: ueData.name,
                code: ueData.code,
                mention: parseInt(ueData.mention),
                semestre: parseInt(ueData.semestre),
                parcours: ueData.parcours.map(id => parseInt(id))
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'unité d\'enseignement');
        }
    },
    updateUe: async (id, ueData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/ues/${id}`, {
                name: ueData.name,
                code: ueData.code,
                mention: parseInt(ueData.mention),
                semestre: parseInt(ueData.semestre),
                parcours: ueData.parcours.map(id => parseInt(id))
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour de l\'unité d\'enseignement');
        }
    },
    deleteUe: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/ues/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'unité d\'enseignement');
        }
    },

    // Ecs
    getEcs: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/ecs`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des éléments constitutifs');
        }
    },
    createEc: async (ecData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/ecs`, {
                name: ecData.name,
                code: ecData.code,
                ue: parseInt(ecData.ue),
                prof: parseInt(ecData.prof),
                coeff: parseInt(ecData.coeff),
                status: ecData.status
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'élément constitutif');
        }
    },
    updateEc: async (id, ecData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/ecs/${id}`, {
                name: ecData.name,
                code: ecData.code,
                ue: parseInt(ecData.ue),
                prof: parseInt(ecData.prof),
                coeff: parseInt(ecData.coeff),
                status: ecData.status
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour de l\'élément constitutif');
        }
    },
    deleteEc: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/ecs/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'élément constitutif');
        }
    },

    getProfs: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/profs?groups[]=prof:read`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            console.error("Error fetching profs:", error);
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des professeurs');
        }
    },

    // Années
    getYears: async () => {
        try {
            const response = await axios.get(`${API_BASE_URL}/years`, {
                headers: getAuthHeaders()
            });
            return response.data['hydra:member'];
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la récupération des années universitaires');
        }
    },
    createYear: async (yearData) => {
        try {
            const response = await axios.post(`${API_BASE_URL}/years`, {
                year: yearData.year,
                current: yearData.current
            }, {
                headers: getAuthHeaders()
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la création de l\'année universitaire');
        }
    },
    updateYear: async (id, yearData) => {
        try {
            const response = await axios.patch(`${API_BASE_URL}/years/${id}`, {
                year: yearData.year,
                current: yearData.current
            }, {
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/merge-patch+json'
                }
            });
            return response.data;
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la mise à jour de l\'année universitaire');
        }
    },
    deleteYear: async (id) => {
        try {
            await axios.delete(`${API_BASE_URL}/years/${id}`, {
                headers: getAuthHeaders()
            });
        } catch (error) {
            throw new Error(error.response?.data?.message || 'Erreur lors de la suppression de l\'année universitaire');
        }
    }
};

export default parametreService;