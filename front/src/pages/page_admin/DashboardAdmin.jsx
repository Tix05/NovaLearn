import React, { useEffect, useState } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { PiStudentFill, PiChalkboardTeacher } from "react-icons/pi";
import { RiUserSettingsLine } from "react-icons/ri";
import { TabView, TabPanel } from 'primereact/tabview';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { Chart } from 'primereact/chart';

const DashboardAdmin = () => {
    const dataStudent = [
        {
            id: 1,
            photo: 'https://randomuser.me/api/portraits/women/1.jpg',
            nom: 'Dupont',
            prenom: 'Marie',
            email: 'marie.dupont@email.com',
            telephone: '06 12 34 56 78',
            niveau: 'L3',
            mention: 'Informatique',
            parcours: 'Développement Web'
        },
        {
            id: 2,
            photo: 'https://randomuser.me/api/portraits/men/1.jpg',
            nom: 'Martin',
            prenom: 'Jean',
            email: 'jean.martin@email.com',
            telephone: '06 23 45 67 89',
            niveau: 'M1',
            mention: 'Mathématiques',
            parcours: 'Analyse'
        },
        // Ajoutez 8 autres étudiants...
        {
            id: 3,
            photo: 'https://randomuser.me/api/portraits/women/2.jpg',
            nom: 'Bernard',
            prenom: 'Sophie',
            email: 'sophie.bernard@email.com',
            telephone: '06 34 56 78 90',
            niveau: 'L2',
            mention: 'Physique',
            parcours: 'Physique quantique'
        },
        {
            id: 4,
            photo: 'https://randomuser.me/api/portraits/men/2.jpg',
            nom: 'Petit',
            prenom: 'Luc',
            email: 'luc.petit@email.com',
            telephone: '06 45 67 89 01',
            niveau: 'M2',
            mention: 'Chimie',
            parcours: 'Chimie organique'
        },
        {
            id: 5,
            photo: 'https://randomuser.me/api/portraits/women/3.jpg',
            nom: 'Leroy',
            prenom: 'Julie',
            email: 'julie.leroy@email.com',
            telephone: '06 56 78 90 12',
            niveau: 'L1',
            mention: 'Biologie',
            parcours: 'Biologie moléculaire'
        },
        {
            id: 6,
            photo: 'https://randomuser.me/api/portraits/men/3.jpg',
            nom: 'Moreau',
            prenom: 'Thomas',
            email: 'thomas.moreau@email.com',
            telephone: '06 67 89 01 23',
            niveau: 'L3',
            mention: 'Informatique',
            parcours: 'IA'
        },
        {
            id: 7,
            photo: 'https://randomuser.me/api/portraits/women/4.jpg',
            nom: 'Simon',
            prenom: 'Laura',
            email: 'laura.simon@email.com',
            telephone: '06 78 90 12 34',
            niveau: 'M1',
            mention: 'Mathématiques',
            parcours: 'Statistiques'
        },
        {
            id: 8,
            photo: 'https://randomuser.me/api/portraits/men/4.jpg',
            nom: 'Laurent',
            prenom: 'Paul',
            email: 'paul.laurent@email.com',
            telephone: '06 89 01 23 45',
            niveau: 'L2',
            mention: 'Physique',
            parcours: 'Astrophysique'
        },
        {
            id: 9,
            photo: 'https://randomuser.me/api/portraits/women/5.jpg',
            nom: 'Michel',
            prenom: 'Claire',
            email: 'claire.michel@email.com',
            telephone: '06 90 12 34 56',
            niveau: 'M2',
            mention: 'Chimie',
            parcours: 'Chimie inorganique'
        },
        {
            id: 10,
            photo: 'https://randomuser.me/api/portraits/men/5.jpg',
            nom: 'Garcia',
            prenom: 'Pierre',
            email: 'pierre.garcia@email.com',
            telephone: '06 01 23 45 67',
            niveau: 'L1',
            mention: 'Biologie',
            parcours: 'Génétique'
        }
    ];

    const dataTeacher = [
        {
            id: 1,
            photo: 'https://randomuser.me/api/portraits/men/6.jpg',
            nom: 'Roux',
            prenom: 'Michel',
            email: 'michel.roux@email.com',
            telephone: '06 12 34 56 78',
            matiere: 'Mathématiques'
        },
        {
            id: 2,
            photo: 'https://randomuser.me/api/portraits/women/6.jpg',
            nom: 'Fournier',
            prenom: 'Isabelle',
            email: 'isabelle.fournier@email.com',
            telephone: '06 23 45 67 89',
            matiere: 'Physique'
        },
        // Ajoutez 8 autres enseignants...
        {
            id: 3,
            photo: 'https://randomuser.me/api/portraits/men/7.jpg',
            nom: 'Lefebvre',
            prenom: 'Philippe',
            email: 'philippe.lefebvre@email.com',
            telephone: '06 34 56 78 90',
            matiere: 'Chimie'
        },
        {
            id: 4,
            photo: 'https://randomuser.me/api/portraits/women/7.jpg',
            nom: 'Dumont',
            prenom: 'Catherine',
            email: 'catherine.dumont@email.com',
            telephone: '06 45 67 89 01',
            matiere: 'Biologie'
        },
        {
            id: 5,
            photo: 'https://randomuser.me/api/portraits/men/8.jpg',
            nom: 'Mercier',
            prenom: 'Jacques',
            email: 'jacques.mercier@email.com',
            telephone: '06 56 78 90 12',
            matiere: 'Informatique'
        },
        {
            id: 6,
            photo: 'https://randomuser.me/api/portraits/women/8.jpg',
            nom: 'Lambert',
            prenom: 'Anne',
            email: 'anne.lambert@email.com',
            telephone: '06 67 89 01 23',
            matiere: 'Philosophie'
        },
        {
            id: 7,
            photo: 'https://randomuser.me/api/portraits/men/9.jpg',
            nom: 'Bonnet',
            prenom: 'François',
            email: 'francois.bonnet@email.com',
            telephone: '06 78 90 12 34',
            matiere: 'Histoire'
        },
        {
            id: 8,
            photo: 'https://randomuser.me/api/portraits/women/9.jpg',
            nom: 'Francois',
            prenom: 'Elodie',
            email: 'elodie.francois@email.com',
            telephone: '06 89 01 23 45',
            matiere: 'Anglais'
        },
        {
            id: 9,
            photo: 'https://randomuser.me/api/portraits/men/10.jpg',
            nom: 'Martinez',
            prenom: 'Antoine',
            email: 'antoine.martinez@email.com',
            telephone: '06 90 12 34 56',
            matiere: 'Economie'
        },
        {
            id: 10,
            photo: 'https://randomuser.me/api/portraits/women/10.jpg',
            nom: 'Legrand',
            prenom: 'Valérie',
            email: 'valerie.legrand@email.com',
            telephone: '06 01 23 45 67',
            matiere: 'Droit'
        }
    ];

    const [globalFilterValue, setGlobalFilterValue] = React.useState('');
    const [chartDataStudent, setChartDataStudent] = useState({});
    const [chartOptionsStudent, setChartOptionsStudent] = useState({});
    const [chartData, setChartData] = useState({});
    const [chartOptions, setChartOptions] = useState({});

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const renderHeaderStudent = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal'>Etudiants</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };

    const renderHeaderTeacher = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal'>Enseignants</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };
    const headerStudent = renderHeaderStudent();
    const headerTeacher = renderHeaderTeacher();

    const actionBodyTemplate = (rowData) => {
        return (
            <Button icon="pi pi-send" rounded severity="success" tooltip="Envoyer message"
                tooltipOptions={{ position: 'left' }} />
        );
    };

    const imageBodyTemplate = (rowData) => {
        return (
            <div className="flex items-center justify-center">
                <div className="relative w-10 h-10">
                    <img
                        src={rowData.photo}
                        alt={`${rowData.nom} ${rowData.prenom}`}
                        className="absolute w-full h-full rounded-full object-cover border-2 border-white shadow-sm"
                    />
                </div>
            </div>
        );
    };

    const nameBodyTemplate = (rowData) => {
        return <span>{rowData.nom} {rowData.prenom}</span>;
    };

    useEffect(() => {
        const documentStyle = getComputedStyle(document.documentElement);
        const textColor = documentStyle.getPropertyValue('--text-color');
        const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
        const surfaceBorder = documentStyle.getPropertyValue('--surface-border');
        const dataStudent = {
            labels: ['Jan', 'Fev', 'Mar', 'Avr', 'May', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Informatique',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: false,
                    borderColor: documentStyle.getPropertyValue('--red-600'),
                    tension: 0.4
                },
                {
                    label: 'Gestion',
                    data: [28, 48, 40, 19, 86, 27, 90],
                    fill: false,
                    borderColor: documentStyle.getPropertyValue('--green-500'),
                    tension: 0.4
                },
                {
                    label: 'Communication',
                    data: [30, 44, 48, 11, 81, 22, 96],
                    fill: false,
                    borderColor: documentStyle.getPropertyValue('--blue-500'),
                    tension: 0.4
                }
            ]
        };
        const options = {
            maintainAspectRatio: false,
            aspectRatio: 0.6,
            plugins: {
                legend: {
                    labels: {
                        color: textColor
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: textColorSecondary
                    },
                    grid: {
                        color: surfaceBorder
                    }
                },
                y: {
                    ticks: {
                        color: textColorSecondary
                    },
                    grid: {
                        color: surfaceBorder
                    }
                }
            }
        };

        setChartDataStudent(dataStudent);
        setChartOptionsStudent(options);
    }, []);

    useEffect(() => {
        const documentStyle = getComputedStyle(document.documentElement);
        const data = {
            labels: ['A', 'B', 'C'],
            datasets: [
                {
                    data: [540, 325, 702],
                    backgroundColor: [
                        documentStyle.getPropertyValue('--blue-500'),
                        documentStyle.getPropertyValue('--yellow-500'),
                        documentStyle.getPropertyValue('--green-500')
                    ],
                    hoverBackgroundColor: [
                        documentStyle.getPropertyValue('--blue-400'),
                        documentStyle.getPropertyValue('--yellow-400'),
                        documentStyle.getPropertyValue('--green-400')
                    ]
                }
            ]
        }
        const options = {
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true
                    }
                }
            }
        };

        setChartData(data);
        setChartOptions(options);
    }, []);

    return (
        <LayoutAdmin>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold text-gray-800 p-5'>Tableau de bord</h1>
                <div className='flex items-center justify-center w-full mb-5'>
                    <div className='flex flex-col items-center justify-center gap-5 ml-10 w-2/4'>
                        <div className='flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full'>
                            <div className='p-3 rounded-md bg-blue-500 text-white'>
                                <PiStudentFill size={30} />
                            </div>
                            <div className='justify-between flex flex-col ml-3'>
                                <p className='font-semibold text-gray-800'>
                                    Etudiants
                                </p>
                                <p className='font-bold text-gray-800'>
                                    {dataStudent.length}
                                </p>
                            </div>
                        </div>
                        <div className='flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full'>
                            <div className='p-3 rounded-md bg-red-800 text-white'>
                                <PiChalkboardTeacher size={30} />
                            </div>
                            <div className='justify-between flex flex-col ml-3'>
                                <p className='font-semibold text-gray-800'>
                                    Professeurs
                                </p>
                                <p className='font-bold text-gray-800'>
                                    {dataTeacher.length}
                                </p>
                            </div>
                        </div>
                        <div className='flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full'>
                            <div className='p-3 rounded-md bg-yellow-600 text-white'>
                                <RiUserSettingsLine size={30} />
                            </div>
                            <div className='justify-between flex flex-col ml-3'>
                                <p className='font-semibold text-gray-800'>
                                    Admin
                                </p>
                                <p className='font-bold text-gray-800'>
                                    1
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className='flex flex-col items-center justify-center w-3/4'>
                        <h1 className='font-semibold text-gray-700 text-lg'>Taux de présence aux examens</h1>
                        <Chart type="pie" data={chartData} options={chartOptions} className="w-[30vw]" />
                    </div>
                </div>
                <div className='flex flex-col items-center justify-center w-full px-5 space-y-5 mb-5'>
                    <div className='w-full p-2'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des connexion à la plateforme</h1>
                        <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                    </div>
                    <div className='w-full p-2'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des étudiants</h1>
                        <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                    </div>
                    <div className='w-full p-2'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des enseignants</h1>
                        <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                    </div>
                </div>
                <div>
                    <TabView className='custom-tabview'>
                        <TabPanel header="Liste des étudiants" className='flex flex-col items-center'>
                            <DataTable value={dataStudent} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={headerStudent} emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" sortable style={{ minWidth: '5rem' }} />
                                <Column field="parcours" header="Parcours" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Liste des enseignants" className='flex flex-col items-center'>
                            <DataTable value={dataTeacher} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={headerTeacher} emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </LayoutAdmin>
    );
};

export default DashboardAdmin;