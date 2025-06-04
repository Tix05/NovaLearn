import React, { useEffect, useState } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { PiStudentFill, PiChalkboardTeacher } from 'react-icons/pi';
import { RiUserSettingsLine } from 'react-icons/ri';
import { TabView, TabPanel } from 'primereact/tabview';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { Chart } from 'primereact/chart';
import { getDashboardData, adminLogout } from '../../Services/adminAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from "react-icons/md";

const DashboardAdmin = () => {
    const [dashboardData, setDashboardData] = useState({
        students: [],
        teachers: [],
        adminCount: 1,
        examPresence: { labels: [], datasets: [] },
        connectionEvolution: { labels: [], datasets: [] },
        studentEvolution: { labels: [], datasets: [] },
        teacherEvolution: { labels: [], datasets: [] },
    });
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [chartOptions, setChartOptions] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
            try {
                const data = await getDashboardData();
                setDashboardData(data);
                setError(null);
            } catch (error) {
                setError(error.message);
                if (error.message.includes('non autorisé')) {
                    adminLogout();
                }
            } finally {
                setLoading(false);
            }
        };

        fetchData();

        // Configuration des options des graphiques
        const documentStyle = getComputedStyle(document.documentElement);
        const textColor = documentStyle.getPropertyValue('--text-color');
        const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
        const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

        setChartOptions({
            pie: {
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            color: textColor,
                        },
                    },
                },
            },
            line: {
                maintainAspectRatio: false,
                aspectRatio: 0.6,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColorSecondary,
                        },
                        grid: {
                            color: surfaceBorder,
                        },
                    },
                    y: {
                        ticks: {
                            color: textColorSecondary,
                        },
                        grid: {
                            color: surfaceBorder,
                        },
                    },
                },
            },
        });
    }, []);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const renderHeaderStudent = () => (
        <div className="flex justify-between items-center">
            <h1 className="text-3xl font-normal">Etudiants</h1>
            <IconField iconPosition="left">
                <InputIcon className="pi pi-search" />
                <InputText
                    value={globalFilterValue}
                    onChange={onGlobalFilterChange}
                    placeholder="Rechercher..."
                    className="custom-input"
                />
            </IconField>
        </div>
    );

    const renderHeaderTeacher = () => (
        <div className="flex justify-between items-center">
            <h1 className="text-3xl font-normal">Enseignants</h1>
            <IconField iconPosition="left">
                <InputIcon className="pi pi-search" />
                <InputText
                    value={globalFilterValue}
                    onChange={onGlobalFilterChange}
                    placeholder="Rechercher..."
                    className="custom-input"
                />
            </IconField>
        </div>
    );

    const actionBodyTemplate = (rowData) => (
        <Button
            icon="pi pi-send"
            rounded
            severity="success"
            tooltip="Envoyer message"
            tooltipOptions={{ position: 'label' }}
        />
    );

    const imageBodyTemplate = (rowData) => (
        <div className="flex items-center justify-center">
            <div className="relative w-10 h-10">
                <img
                    src={rowData.photo}
                    alt={`${rowData.nom} ${rowData.prenom}`}
                    className="absolute w-full h-full rounded-full object-cover border-2 border-gray rounded"
                />
            </div>
        </div>
    );

    const nameBodyTemplate = (rowData) => (
        <span>{rowData.nom} {rowData.prenom}</span>
    );

    // Helper function to check if a chart has no data
    const isChartEmpty = (chartData) => {
        if (!chartData || !chartData.datasets || chartData.datasets.length === 0) {
            return true;
        }
        if (chartData.labels.length === 0 || chartData.labels[0] === 'Aucune donnée') {
            return true;
        }
        return chartData.datasets.every(dataset =>
            dataset.data.every(value => value === 0)
        );
    };

    if (loading) {
        return (
            <LayoutAdmin>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </LayoutAdmin>
        );
    }

    if (error) {
        return (
            <LayoutAdmin>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className='text-xl font-bold'>Erreur lors du chargement des données</p>
                    <p className='text-lg font-semibold'>{error}</p>
                </div>
            </LayoutAdmin>
        );
    }

    return (
        <LayoutAdmin>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className="text-4xl font-semibold text-gray-800 p-5">Tableau de bord</h1>
                <div className="flex items-center justify-center w-full mb-5">
                    <div className="flex flex-col items-center justify-center gap-5 ml-10 w-2/4">
                        <div className="flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full">
                            <div className="p-3 rounded-md bg-blue-500 text-white">
                                <PiStudentFill size={30} />
                            </div>
                            <div className="justify-between flex flex-col ml-3">
                                <p className="font-semibold text-gray-800">Etudiants</p>
                                <p className="font-bold text-gray-800">{dashboardData.students.length}</p>
                            </div>
                        </div>
                        <div className="flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full">
                            <div className="p-3 rounded-md bg-red-800 text-white">
                                <PiChalkboardTeacher size={30} />
                            </div>
                            <div className="justify-between flex flex-col ml-3">
                                <p className="font-semibold text-gray-800">Professeurs</p>
                                <p className="font-bold text-gray-800">{dashboardData.teachers.length}</p>
                            </div>
                        </div>
                        <div className="flex bg-white border-[1px] shadow-md p-2 rounded-lg w-full">
                            <div className="p-3 rounded-md bg-yellow-600 text-white">
                                <RiUserSettingsLine size={30} />
                            </div>
                            <div className="justify-between flex flex-col ml-3">
                                <p className="font-semibold text-gray-800">Admin</p>
                                <p className="font-bold text-gray-800">{dashboardData.adminCount}</p>
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-col items-center justify-center w-3/4">
                        <h1 className="font-semibold text-gray-700 text-lg">Taux de présence aux examens</h1>
                        {isChartEmpty(dashboardData.examPresence) ? (
                            <div className="flex items-center justify-center w-full h-[30vw]">
                                <p className="text-lg text-gray-500">Aucune donnée disponible pour le moment.</p>
                            </div>
                        ) : (
                            <Chart type="pie" data={dashboardData.examPresence} options={chartOptions.pie} className="w-[30vw]" />
                        )}
                    </div>
                </div>
                <div className="flex flex-col items-center justify-center w-full px-5 space-y-5 mb-5">
                    <div className="w-full p-2">
                        <h1 className="text-xl py-3 font-bold text-gray-700 text-center">Evolution des connexions à la plateforme</h1>
                        {isChartEmpty(dashboardData.connectionEvolution) ? (
                            <div className="flex items-center justify-center w-full h-[300px]">
                                <p className="text-lg text-gray-500">Aucune connexion enregistrée pour le moment.</p>
                            </div>
                        ) : (
                            <Chart type="line" data={dashboardData.connectionEvolution} options={chartOptions.line} style={{ height: '300px' }} />
                        )}
                    </div>
                    <div className="w-full p-2">
                        <h1 className="text-xl py-3 font-bold text-gray-700 text-center">Evolution des étudiants</h1>
                        {isChartEmpty(dashboardData.studentEvolution) ? (
                            <div className="flex items-center justify-center w-full h-[300px]">
                                <p className="text-lg text-gray-500">Aucune inscription d'étudiant enregistrée pour le moment.</p>
                            </div>
                        ) : (
                            <Chart type="line" data={dashboardData.studentEvolution} options={chartOptions.line} style={{ height: '300px' }} />
                        )}
                    </div>
                    <div className="w-full p-2">
                        <h1 className="text-xl py-3 font-bold text-gray-700 text-center">Evolution des enseignants</h1>
                        {isChartEmpty(dashboardData.teacherEvolution) ? (
                            <div className="flex items-center justify-center w-full h-[300px]">
                                <p className="text-lg text-gray-500">Aucun enseignant enregistré pour le moment.</p>
                            </div>
                        ) : (
                            <Chart type="line" data={dashboardData.teacherEvolution} options={chartOptions.line} style={{ height: '300px' }} />
                        )}
                    </div>
                </div>
                <div>
                    <TabView className="custom-tabview">
                        <TabPanel header="Liste des étudiants" className="flex flex-col items-center">
                            <DataTable
                                value={dashboardData.students}
                                paginator
                                rows={7}
                                dataKey="id"
                                sortField="nom"
                                sortOrder={1}
                                globalFilter={globalFilterValue}
                                header={renderHeaderStudent()}
                                emptyMessage="Aucune donnée trouvée."
                                className="w-full"
                            >
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
                        <TabPanel header="Liste des enseignants" className="flex flex-col items-center">
                            <DataTable
                                value={dashboardData.teachers}
                                paginator
                                rows={7}
                                dataKey="id"
                                sortField="nom"
                                sortOrder={1}
                                globalFilter={globalFilterValue}
                                header={renderHeaderTeacher()}
                                emptyMessage="Aucune donnée trouvée."
                                className="w-full"
                            >
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="matiere" header="Matière" sortable style={{ minWidth: '5rem' }} />
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