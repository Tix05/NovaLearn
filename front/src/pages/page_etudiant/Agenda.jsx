import React, { useState } from 'react';
import { TabView, TabPanel } from 'primereact/tabview';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';

const DEMO_IMAGES = {
    maths: 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    physique: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    conference: 'https://images.unsplash.com/photo-1579353977828-2a4eab540b9a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    chimie: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    informatique: 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80'
};

const DEMO_VIDEOS = {
    cours: 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
    conference: 'https://samplelib.com/lib/preview/mp4/sample-10s.mp4',
    examen: 'https://samplelib.com/lib/preview/mp4/sample-15s.mp4'
};

export default function Agenda() {
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const periodeOptions = [
        { label: 'Tout', value: 'Tout' },
        { label: 'Semaine', value: 'Semaine' },
        { label: 'Mois', value: 'Mois' },
        { label: 'Trimestre', value: 'Trimestre' },
        { label: 'Semestre', value: 'Semestre' },
    ];

    // Fonction pour générer des dates de test
    const generateTestDate = (daysFromNow) => {
        const date = new Date();
        date.setDate(date.getDate() + daysFromNow);
        return date.toISOString().split('T')[0];
    };

    const [coursData] = useState([
        {
            id: 1,
            titre: 'Cours de Mathématiques',
            date: generateTestDate(1),
            description: 'Introduction aux équations différentielles',
            professeur: 'Prof. Dupont',
            media: {
                type: 'image',
                url: DEMO_IMAGES.maths,
                name: 'maths.jpg'
            }
        },
        {
            id: 2,
            titre: 'Cours de Physique',
            date: generateTestDate(3),
            description: 'Mécanique quantique avancée',
            professeur: 'Prof. Martin',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.cours,
                thumbnail: DEMO_IMAGES.physique,
                name: 'physique.mp4'
            }
        },
        {
            id: 3,
            titre: 'Cours de Chimie',
            date: generateTestDate(10),
            description: 'Chimie organique - Les alcènes',
            professeur: 'Prof. Legrand',
            media: {
                type: 'image',
                url: DEMO_IMAGES.chimie,
                name: 'chimie.jpg'
            }
        },
        {
            id: 4,
            titre: 'Cours d\'Informatique',
            date: generateTestDate(30),
            description: 'Algorithmes avancés',
            professeur: 'Prof. Dubois',
            media: {
                type: 'image',
                url: DEMO_IMAGES.informatique,
                name: 'informatique.jpg'
            }
        },
        {
            id: 5,
            titre: 'Cours de Biologie',
            date: generateTestDate(90),
            description: 'Génétique moléculaire',
            professeur: 'Prof. Bernard',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.cours,
                thumbnail: DEMO_IMAGES.physique,
                name: 'biologie.mp4'
            }
        }
    ]);

    const [examensData] = useState([
        {
            id: 1,
            titre: 'Examen de Mathématiques',
            date: generateTestDate(5),
            description: 'Examen final - Partie 1',
            professeur: 'Prof. Dupont',
            media: {
                type: 'image',
                url: DEMO_IMAGES.maths,
                name: 'examen_maths.jpg'
            }
        },
        {
            id: 2,
            titre: 'Examen de Physique',
            date: generateTestDate(8),
            description: 'Examen pratique',
            professeur: 'Prof. Martin',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.examen,
                thumbnail: DEMO_IMAGES.physique,
                name: 'examen_physique.mp4'
            }
        },
        {
            id: 3,
            titre: 'Examen de Chimie',
            date: generateTestDate(60),
            description: 'Examen théorique',
            professeur: 'Prof. Legrand',
            media: {
                type: 'image',
                url: DEMO_IMAGES.chimie,
                name: 'examen_chimie.jpg'
            }
        }
    ]);

    const [evenementsData] = useState([
        {
            id: 1,
            titre: 'Conférence sur l\'IA',
            date: generateTestDate(2),
            description: 'Conférence avec un expert en IA',
            organisateur: 'Dr. Smith',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.conference,
                thumbnail: DEMO_IMAGES.conference,
                name: 'conference_ia.mp4'
            }
        },
        {
            id: 2,
            titre: 'Journée portes ouvertes',
            date: generateTestDate(15),
            description: 'Découverte des laboratoires de recherche',
            organisateur: 'Dr. Johnson',
            media: {
                type: 'image',
                url: DEMO_IMAGES.conference,
                name: 'portes_ouvertes.jpg'
            }
        },
        {
            id: 3,
            titre: 'Séminaire de recherche',
            date: generateTestDate(45),
            description: 'Avancées récentes en physique quantique',
            organisateur: 'Prof. Einstein',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.conference,
                thumbnail: DEMO_IMAGES.physique,
                name: 'seminaire_physique.mp4'
            }
        },
        {
            id: 4,
            titre: 'Remise des diplômes',
            date: generateTestDate(120),
            description: 'Cérémonie annuelle de remise des diplômes',
            organisateur: 'Directeur Université',
            media: {
                type: 'image',
                url: DEMO_IMAGES.conference,
                name: 'remise_diplomes.jpg'
            }
        }
    ]);

    const filterData = (data) => {
        if (periodeFilter === 'Tout') {
            return data;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const endDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine':
                endDate.setDate(today.getDate() + 7);
                break;
            case 'Mois':
                endDate.setMonth(today.getMonth() + 1);
                break;
            case 'Trimestre':
                endDate.setMonth(today.getMonth() + 3);
                break;
            case 'Semestre':
                endDate.setMonth(today.getMonth() + 6);
                break;
            default:
                return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            itemDate.setHours(0, 0, 0, 0);
            return itemDate >= today && itemDate <= endDate;
        });
    };

    const renderItem = (item) => (
        <div
            key={item.id}
            className="flex flex-col md:flex-row border-[1px] mb-4 w-full md:w-4/5 rounded-lg bg-white shadow-md"
        >
            <div className="flex flex-col p-4 text-center md:w-28 flex-shrink-0">
                <h1 className="text-5xl font-bold text-gray-800">{new Date(item.date).getDate()}</h1>
                <h2 className="text-lg font-semibold text-gray-700 uppercase">
                    {new Date(item.date).toLocaleString('default', { month: 'short' })}
                </h2>
                <h3 className="text-sm text-gray-500">
                    {new Date(item.date).toLocaleString('default', { weekday: 'short' })}
                </h3>
            </div>

            <Divider layout="vertical" className="hidden md:block h-auto" />

            <div className="flex flex-col p-4 flex-1 min-h-[180px]">
                <div className="flex justify-between items-start mb-2">
                    <h1 className="text-xl font-semibold text-gray-800 truncate flex-1">
                        {item.titre}
                    </h1>
                </div>

                <div className="mb-3">
                    <p className="text-gray-600 whitespace-pre-wrap break-words">
                        {item.description}
                    </p>
                </div>

                {item.media && (
                    <div className="mt-auto">
                        {item.media.type === 'image' ? (
                            <div className="border border-gray-200 rounded-lg overflow-hidden">
                                <img
                                    src={item.media.url}
                                    alt={item.media.name || 'Image'}
                                    className="w-full h-auto"
                                />
                                {item.media.name && (
                                    <div className="p-2 bg-gray-50 text-sm text-gray-600 truncate">
                                        {item.media.name}
                                    </div>
                                )}
                            </div>
                        ) : item.media.type === 'video' ? (
                            <div className="mt-3">
                                <div className="relative pt-[56.25%] bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                    <video
                                        controls
                                        className="absolute inset-0 w-full h-full"
                                        poster={item.media.thumbnail}
                                    >
                                        <source src={item.media.url} type="video/mp4" />
                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                    </video>
                                </div>
                                {item.media.name && (
                                    <div className="mt-1 text-sm text-gray-600 truncate">
                                        {item.media.name}
                                    </div>
                                )}
                            </div>
                        ) : (
                            <div className="p-3 bg-gray-50 rounded-lg border border-gray-200 flex items-center">
                                <i className="pi pi-file text-gray-500 text-xl mr-3"></i>
                                <span className="text-gray-700">
                                    {item.media.name || 'Fichier joint'}
                                </span>
                            </div>
                        )}
                    </div>
                )}

                <div className="mt-3 pt-2 border-t border-gray-100">
                    <p className="text-sm font-medium text-gray-700">
                        {item.professeur || item.organisateur || 'Non spécifié'}
                    </p>
                </div>
            </div>
        </div>
    );

    const renderFilterSection = () => (
        <div className="mb-6 flex gap-2 w-60">
            <Dropdown
                value={periodeFilter}
                onChange={(e) => setPeriodeFilter(e.value)}
                options={periodeOptions}
                optionLabel="label"
                placeholder="Période"
                panelClassName="font-poppins text-sm"
                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
            />
        </div>
    );

    return (
        <Layout>
            <div className="card custom-scrollbar h-[90vh] overflow-y-auto">
                <TabView className='custom-tabview'>
                    <TabPanel header="Cours" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(coursData).length > 0 ? (
                                filterData(coursData).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun cours prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Examens" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(examensData).length > 0 ? (
                                filterData(examensData).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun examen prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Evènements" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(evenementsData).length > 0 ? (
                                filterData(evenementsData).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun évènement prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                </TabView>
            </div>
        </Layout>
    );
}