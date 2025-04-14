import icon from '../images/icon-communication.png';

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
                ues: [
                    {
                        id: 'UE1',
                        nom: 'UE Fondamentale',
                        cours: [
                            {
                                id: 1,
                                titre: 'Mathématiques',
                                credit: 5,
                                description: 'Description du cours de Mathématiques...',
                                supports: [
                                    { type: 'document', nom: 'math-doc1.pdf', titre: 'Cours de Mathématiques - Algèbre' },
                                    { type: 'document', nom: 'math-doc2.pdf', titre: 'Cours de Mathématiques - Géométrie' }
                                ]
                            },
                            {
                                id: 2,
                                titre: 'Physique',
                                credit: 4,
                                description: 'Description du cours de Physique...',
                                supports: [
                                    { type: 'document', nom: 'physique-doc1.pdf', titre: 'Cours de Physique - Mécanique' }
                                ]
                            }
                        ]
                    },
                    {
                        id: 'UE2',
                        nom: 'UE Méthodologique',
                        cours: [
                            {
                                id: 3,
                                titre: 'Méthodologie',
                                credit: 3,
                                description: 'Description du cours de Méthodologie...',
                                supports: []
                            }
                        ]
                    }
                ]
            },
            {
                id: 'S2',
                intitule: 'Semestre 2',
                ues: [
                    {
                        id: 'UE3',
                        nom: 'UE Informatique',
                        cours: [
                            {
                                id: 4,
                                titre: 'Algorithmique',
                                credit: 6,
                                description: "Description du cours d'Algorithmique...",
                                supports: []
                            }
                        ]
                    }
                ]
            }
        ]
    }
];