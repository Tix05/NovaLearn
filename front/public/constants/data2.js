import icon from '../images/icon-communication.png'
export const mentions = [
    {
        id: 1,
        nom: 'COMMUNICATION',
        niveau: 'L1',
        parcours: "INTRODUCTION AUX SCIENCES DE L'INFORMATION ET DE LA COMMUNICATION",
        matricule: '123456',
        icon: icon,
        semestres: [
            {
                id: 'S1',
                intitule: 'Semestre 1',
                cours: [
                    {
                        id: 1,
                        titre: 'Mathématiques',
                        credit: 5,
                        description: 'Description du cours de Mathématiques...',
                        supports: [
                            { type: 'document', nom: 'math-doc1.pdf', titre: 'Cours de Mathématiques - Algèbre' },
                            { type: 'document', nom: 'math-doc2.pdf', titre: 'Cours de Mathématiques - Géométrie' },
                            { type: 'audio', nom: 'math-audio1.mp3', titre: 'Introduction aux Mathématiques - Algèbre' },
                            { type: 'video', nom: 'math-video1.mp4', titre: 'Exercices de Mathématiques - Fonctions' },
                        ],

                    },
                    {
                        id: 2,
                        titre: 'Physique',
                        credit: 4,
                        description: 'Description du cours de Physique...',
                        supports: [
                            { type: 'document', nom: 'document2.pdf' },
                            { type: 'audio', nom: 'audio2.mp3' },
                            { type: 'video', nom: 'video2.mp4' },
                        ],
                    },
                ],
            },
            {
                id: 'S2',
                intitule: 'Semestre 2',
                cours: [
                    {
                        id: 3,
                        titre: 'Informatique',
                        credit: 6,
                        description: "Description du cours d'Informatique...",
                        supports: [
                            { type: 'document', nom: 'document3.pdf' },
                            { type: 'audio', nom: 'audio3.mp3' },
                            { type: 'video', nom: 'video3.mp4' },
                        ],
                    },
                ],
            },
        ],
    },
];