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
                        id: 'S2',
                        intitule: 'Semestre 2',
                        cours: [
                            {
                                id: 3,
                                titre: 'Journalisme numérique',
                                credit: 6,
                                description: 'Les nouvelles pratiques journalistiques',
                                supports: [
                                    { type: 'document', nom: 'journalisme-num.pdf', titre: 'Manuel de référence' },
                                    { type: 'audio', nom: 'interview.mp3', titre: 'Interview professionnelle' }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                id: 'L2',
                nom: 'L2',
                parcours: "COMMUNICATION AVANCÉE",
                semestres: [
                    {
                        id: 'S3',
                        intitule: 'Semestre 3',
                        cours: [
                            {
                                id: 4,
                                titre: 'Communication digitale',
                                credit: 6,
                                description: 'Stratégies de communication en ligne',
                                supports: [
                                    { type: 'document', nom: 'com-digital.pdf', titre: 'Cours théorique' }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    },
    {
        id: 2,
        nom: 'INFORMATIQUE',
        icon: iconInfo,
        niveaux: [
            {
                id: 'L1',
                nom: 'L1',
                parcours: "FONDAMENTAUX DE L'INFORMATIQUE",
                semestres: [
                    {
                        id: 'S1',
                        intitule: 'Semestre 1',
                        cours: [
                            {
                                id: 5,
                                titre: 'Algorithmique',
                                credit: 5,
                                description: 'Bases de la programmation',
                                supports: [
                                    { type: 'document', nom: 'algo-base.pdf', titre: 'Cours d\'introduction' }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                id: 'L2',
                nom: 'L2',
                parcours: "DÉVELOPPEMENT D'APPLICATIONS",
                semestres: [
                    {
                        id: 'S3',
                        intitule: 'Semestre 3',
                        cours: [
                            {
                                id: 6,
                                titre: 'Bases de données',
                                credit: 5,
                                description: 'Modélisation et SQL',
                                supports: [
                                    { type: 'document', nom: 'bdd-cours.pdf', titre: 'Support de cours' }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    },
    {
        id: 3,
        nom: 'ECONOMIE',
        icon: iconEco,
        niveaux: [
            {
                id: 'L1',
                nom: 'L1',
                parcours: "ÉCONOMIE GÉNÉRALE",
                semestres: [
                    {
                        id: 'S1',
                        intitule: 'Semestre 1',
                        cours: [
                            {
                                id: 7,
                                titre: 'Microéconomie',
                                credit: 5,
                                description: 'Théories des marchés',
                                supports: [
                                    { type: 'document', nom: 'micro-cours.pdf', titre: 'Manuel de cours' }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                id: 'L3',
                nom: 'L3',
                parcours: "ÉCONOMIE INTERNATIONALE",
                semestres: [
                    {
                        id: 'S5',
                        intitule: 'Semestre 5',
                        cours: [
                            {
                                id: 8,
                                titre: 'Commerce international',
                                credit: 6,
                                description: 'Stratégies d\'exportation',
                                supports: [
                                    { type: 'document', nom: 'commerce-int.pdf', titre: 'Études de cas' }
                                ]
                            }
                        ]
                    }
                ]
            }
        ]
    }
];