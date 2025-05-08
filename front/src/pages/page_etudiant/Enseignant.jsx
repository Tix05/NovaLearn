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
            }
        };

        fetchData();
    }, []);

    useEffect(() => {
        // Données fictives pour un étudiant en L1 Informatique
        const examProgress = [
            {
                course: 'Algorithmique',
                semesters: [
                    { name: 'S1', score: 60 },
                    { name: 'S2', score: 75 },
                ],
            },
            {
                course: 'Base de données',
                semesters: [
                    { name: 'S1', score: 45 },
                    { name: 'S2', score: 65 },
                ],
            },
            {
                course: 'Réseaux',
                semesters: [
                    { name: 'S1', score: 70 },
                    { name: 'S2', score: 80 },
                ],
            },
            {
                course: 'Mathématiques',
                semesters: [
                    { name: 'S1', score: 55 },
                    { name: 'S2', score: 70 },
                ],
            },
            {
                course: 'Programmation',
                semesters: [
                    { name: 'S1', score: 65 },
                    { name: 'S2', score: 85 },
                ],
            },
        ];

        const documentStyle = getComputedStyle(document.documentElement);
        const textColor = documentStyle.getPropertyValue('--text-color');
        const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
        const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

        // Matières (x-axis labels)
        const courses = examProgress.map(course => course.course);

        // Datasets pour S1 et S2
        const datasets = [
            {
                label: 'Semestre 1',
                backgroundColor: documentStyle.getPropertyValue('--red-400'),
                data: examProgress.map(course => course.semesters.find(s => s.name === 'S1').score),
            },
            {
                label: 'Semestre 2',
                backgroundColor: documentStyle.getPropertyValue('--green-500'),
                data: examProgress.map(course => course.semesters.find(s => s.name === 'S2').score),
            },
        ];

        const dataStudent = {
            labels: courses,
            datasets,
        };

        const options = {
            maintainAspectRatio: false,
            aspectRatio: 0.6,
            plugins: {
                legend: {
                    labels: {
                        color: textColor,
                        font: { size: 14 },
                    },
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: (context) => {
                            const score = context.parsed.y;
                            const semester = context.dataset.label;
                            const advice = score < 50 ? ' (Note basse, envisagez de réviser)' : '';
                            return `${semester} - ${context.label}: ${score}/100${advice}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Matières',
                        color: textColor,
                        font: { size: 16 },
                    },
                    ticks: {
                        color: textColorSecondary,
                        font: { size: 12 },
                    },
                    grid: {
                        color: surfaceBorder,
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Note (/100)',
                        color: textColor,
                        font: { size: 16 },
                    },
                    ticks: {
                        color: textColorSecondary,
                        font: { size: 12 },
                        beginAtZero: true,
                        max: 100,
                    },
                    grid: {
                        color: surfaceBorder,
                    },
                    // Ligne de référence pour la note minimale de passage (50/100)
                    afterBuildTicks: (axis) => {
                        axis.ticks.push({ value: 50 });
                    },
                    afterDraw: (chart) => {
                        const ctx = chart.ctx;
                        const yAxis = chart.scales.y;
                        const yValue = yAxis.getPixelForValue(50);
                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(yAxis.left, yValue);
                        ctx.lineTo(yAxis.right, yValue);
                        ctx.strokeStyle = documentStyle.getPropertyValue('--red-500');
                        ctx.lineWidth = 2;
                        ctx.setLineDash([5, 5]);
                        ctx.stroke();
                        ctx.restore();
                    },
                },
            },
        };

        setChartDataStudent(dataStudent);
        setChartOptionsStudent(options);
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
                <div className='w-full p-10'>
                    <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Notes par matière pour les semestres 1 et 2 (L1 Informatique)</h1>
                    <Chart type="bar" data={chartDataStudent} options={chartOptionsStudent} />
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