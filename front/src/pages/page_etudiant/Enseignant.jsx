import React, { useState, useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import Layout from '../../components/Layout';
import { Button } from 'primereact/button';
import { getEnseignantsByEtudiant } from '../../Services/enseignantService';
import { Chart } from 'primereact/chart';

export default function Enseignant() {
    const [data, setData] = useState([]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [chartDataStudent, setChartDataStudent] = useState({});
    const [chartOptionsStudent, setChartOptionsStudent] = useState({});

    useEffect(() => {
        const fetchData = async () => {
            try {
                const enseignants = await getEnseignantsByEtudiant();
                setData(enseignants);
            } catch (error) {
                console.error("Erreur lors du chargement des enseignants:", error);
                // Vous pouvez ajouter ici une notification d'erreur si nécessaire
            }
        };

        fetchData();
    }, []);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
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




    const imageBodyTemplate = (rowData) => {
        return (
            <div className="flex justify-center">
                <img
                    src={rowData.profil}
                    alt={rowData.nom}
                    className="w-10 h-10 rounded-full object-cover border-2 border-white shadow"
                    onError={(e) => {
                        e.target.src = 'https://www.gravatar.com/avatar/default?s=200&d=mm';
                        e.target.onerror = null;
                    }}
                />
            </div>
        );
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='md:text-3xl text-xl font-normal'>Mes enseignants</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };

    const header = renderHeader();

    const actionBodyTemplate = (rowData) => {
        return (
            <Button icon="pi pi-send" rounded severity="success" tooltip="Envoyer message"
                tooltipOptions={{ position: 'top' }} />
        );
    };

    return (
        <Layout>
            <div className='custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <div className='w-full p-10'>
                    <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des étudiants</h1>
                    <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                </div>
                <DataTable
                    value={data}
                    paginator
                    rows={10}
                    dataKey="id"
                    sortField="nom"
                    sortOrder={1}
                    globalFilter={globalFilterValue}
                    header={header}
                    emptyMessage="Aucune donnée trouvée."
                >
                    <Column field="profil" header="Photo" body={imageBodyTemplate} style={{ width: '5rem' }} />
                    <Column field="nom" header="Nom et Prénom" sortable style={{ minWidth: '5rem' }} />
                    <Column field="ec" header="EC" sortable style={{ minWidth: '5rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                    <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </Layout>
    );
}