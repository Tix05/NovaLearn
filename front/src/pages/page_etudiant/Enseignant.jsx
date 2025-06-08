import React, { useState, useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import Layout from '../../components/Layout';
import { Button } from 'primereact/button';
import { getEnseignantsByEtudiant, getNotesByEtudiant } from '../../Services/enseignantService';
import { Chart } from 'primereact/chart';
import { ProgressSpinner } from 'primereact/progressspinner';
import { Message } from 'primereact/message';
import { MdErrorOutline } from 'react-icons/md';

export default function Enseignant() {
    const [data, setData] = useState([]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [chartDataStudent, setChartDataStudent] = useState(null);
    const [chartOptionsStudent, setChartOptionsStudent] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const enseignants = await getEnseignantsByEtudiant();
                setData(enseignants);
            } catch (error) {
                console.error("Erreur lors du chargement des enseignants:", error);
            }
        };

        fetchData();
    }, []);

    useEffect(() => {
        const fetchNotes = async () => {
            try {
                setLoading(true);
                const notes = await getNotesByEtudiant();

                if (!notes || notes.length === 0) {
                    setError('Aucune note trouvée pour cet étudiant.');
                    setLoading(false);
                    return;
                }

                const documentStyle = getComputedStyle(document.documentElement);
                const textColor = documentStyle.getPropertyValue('--text-color');
                const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
                const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

                const ecs = notes.map(item => item.ec);
                const dataset = {
                    label: 'Notes des examens',
                    backgroundColor: documentStyle.getPropertyValue('--blue-500'),
                    data: notes.map(item => item.note),
                    barThickness: 20
                };

                const dataStudent = {
                    labels: ecs,
                    datasets: [dataset]
                };

                const options = {
                    maintainAspectRatio: false,
                    aspectRatio: 0.6,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor,
                                font: { size: 14 }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: (context) => {
                                    const score = context.parsed.y;
                                    const ec = context.label;
                                    const advice = score < 10 ? ' (Note basse, envisagez de réviser)' : '';
                                    return `${ec}: ${score}/20${advice}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Éléments Constitutifs (EC)',
                                color: textColor,
                                font: { size: 16 }
                            },
                            ticks: {
                                color: textColorSecondary,
                                font: { size: 12 }
                            },
                            grid: {
                                color: surfaceBorder
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Note (/20)',
                                color: textColor,
                                font: { size: 16 }
                            },
                            ticks: {
                                color: textColorSecondary,
                                font: { size: 12 },
                                beginAtZero: true,
                                max: 20
                            },
                            grid: {
                                color: surfaceBorder
                            },
                            afterBuildTicks: (axis) => {
                                axis.ticks.push({ value: 10 });
                            },
                            afterDraw: (chart) => {
                                const ctx = chart.ctx;
                                const yAxis = chart.scales.y;
                                const yValue = yAxis.getPixelForValue(10);
                                ctx.save();
                                ctx.beginPath();
                                ctx.moveTo(yAxis.left, yValue);
                                ctx.lineTo(yAxis.right, yValue);
                                ctx.strokeStyle = documentStyle.getPropertyValue('--red-500');
                                ctx.lineWidth = 2;
                                ctx.setLineDash([5, 5]);
                                ctx.stroke();
                                ctx.restore();
                            }
                        }
                    }
                };

                setChartDataStudent(dataStudent);
                setChartOptionsStudent(options);
                setLoading(false);
            } catch (error) {
                console.error("Erreur lors du chargement des notes:", error);
                setError('Erreur lors de la récupération des notes. Veuillez réessayer.');
                setLoading(false);
            }
        };

        fetchNotes();
    }, []);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

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
                <div className='w-full p-10 flex flex-col items-center justify-center'>
                    <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Notes par EC</h1>
                    {loading ? (
                        <ProgressSpinner style={{ width: '50px', height: '50px' }} />
                    ) : error ? (
                        <div className="flex justify-center items-center mt-2 text-red-500 font-semibold space-x-1">
                            <MdErrorOutline size={20} />
                            <p >{error}</p>

                        </div>
                    ) : chartDataStudent && chartOptionsStudent ? (
                        <Chart type="bar" data={chartDataStudent} options={chartOptionsStudent} className='w-full' />
                    ) : (
                        <div className="flex justify-center items-center mt-2 text-gray-500 font-semibold space-x-1">
                            <p>Aucune donnée de graphique disponible.</p>
                        </div>
                    )}
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