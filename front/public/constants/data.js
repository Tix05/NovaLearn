import iconCom from '../images/icon-communication.png';
import iconInfo from '../images/icon-communication.png';
import iconEco from '../images/icon-communication.png';

export const mentions = [
    {
        id: 1,
        nom: 'COMMUNICATION',
        icon: iconCom,
        niveaux: [
            {
                id: 'L1',
                nom: 'L1',
                parcours: "INTRODUCTION AUX SCIENCES DE L'INFORMATION ET DE LA COMMUNICATION",
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
                                        titre: 'Communication de base',
                                        credit: 5,
                                        description: 'Fondamentaux de la communication interpersonnelle',
                                        supports: [
                                            { type: 'document', nom: 'com-base.pdf', titre: 'Cours de communication de base' },
                                            { type: 'video', nom: 'com-video1.mp4', titre: 'Exercices pratiques' }
                                        ]
                                    },
                                    {
                                        id: 2,
                                        titre: 'Sociologie des médias',
                                        credit: 4,
                                        description: 'Introduction à l\'analyse des médias',
                                        supports: [
                                            { type: 'document', nom: 'socio-medias.pdf', titre: 'Support de cours complet' }
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
                                        titre: 'Méthodes de recherche',
                                        credit: 3,
                                        description: 'Introduction aux méthodes qualitatives et quantitatives',
                                        supports: []
                                    }
                                ]
                            }
                        ]
                    },
                    // ... autres semestres
                ]
            },
            // ... autres niveaux
        ]
    },
    // ... autres mentions
];