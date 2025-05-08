// src/services/agendaService.js

import axios from 'axios';
import { getToken } from './authService';

const API_URL = 'http://localhost:8000/api';

export const getStudentAgenda = async () => {
    try {
        const response = await axios.get(`${API_URL}/agenda/student`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`
            }
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching student agenda:", error);
        throw new Error('Erreur lors de la récupération de l\'agenda');
    }
};

export const getCurrentUser = () => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
};

export const getStudentAgendas = async () => {
    try {
        // En production, vous utiliserez votre véritable endpoint API
        // const response = await fetch('/api/agenda');
        // return await response.json();

        // Simulation de données pour le développement
        return {
            cours: [
                {
                    id: 1,
                    titre: "Introduction à React",
                    description: "Les bases de React et les composants",
                    date: new Date(Date.now() + 86400000).toISOString(), // Demain
                    type: "Cours",
                    mention: "Informatique",
                    parcours: "Licence",
                    niveau: "L1"
                }
            ],
            examens: [
                {
                    id: 101,
                    titre: "Examen de Mathématiques",
                    description: "Examen couvrant les chapitres 1 à 4",
                    date: new Date(Date.now() + 172800000).toISOString(), // Après-demain
                    type: "Examen",
                    mention: "Mathématiques",
                    parcours: "Licence",
                    niveau: "L1",
                    lienExamen: "math-101" // Identifiant de l'examen
                }
            ],
            evenements: [
                {
                    id: 201,
                    titre: "Réunion d'information",
                    description: "Présentation des projets de fin d'année",
                    date: new Date(Date.now() + 259200000).toISOString(), // Dans 3 jours
                    type: "Évènement"
                }
            ]
        };
    } catch (error) {
        console.error("Erreur lors de la récupération de l'agenda", error);
        throw error;
    }
};