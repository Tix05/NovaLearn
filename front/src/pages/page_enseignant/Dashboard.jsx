import React, { useState, useEffect } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { BiSolidMessageRounded } from "react-icons/bi";
import { FaCircleArrowRight } from "react-icons/fa6";
import { Link } from 'react-router-dom';
import { Chart } from 'primereact/chart';
import { getTeacherDashboardStats } from '../../Services/teacherAuthService';
import { PiStudent } from 'react-icons/pi';
import { MdOutlineLibraryBooks } from 'react-icons/md';

const Dashboard = () => {
    const [mentionsCount, setMentionsCount] = useState(0);
    const [mentionsStats, setMentionsStats] = useState([]);
    const [chartDataStudent, setChartDataStudent] = useState({});
    const [chartDataFiles, setChartDataFiles] = useState({});
    const [chartOptions, setChartOptions] = useState({});

    const colors = [
        getComputedStyle(document.documentElement).getPropertyValue('--red-600'),
        getComputedStyle(document.documentElement).getPropertyValue('--green-500'),
        getComputedStyle(document.documentElement).getPropertyValue('--blue-500'),
        getComputedStyle(document.documentElement).getPropertyValue('--yellow-500'),
        getComputedStyle(document.documentElement).getPropertyValue('--purple-500'),
    ];

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const data = await getTeacherDashboardStats();
                setMentionsCount(data.mentions_count);
                setMentionsStats(data.mentions);
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        };
        fetchStats();
    }, []);

    useEffect(() => {
        const documentStyle = getComputedStyle(document.documentElement);
        const textColor = documentStyle.getPropertyValue('--text-color');
        const textColorSecondary = documentStyle.getPropertyValue('--text-color-secondary');
        const surfaceBorder = documentStyle.getPropertyValue('--surface-border');

        // Graphique 1 : Répartition des étudiants par mention
        const dataStudent = {
            labels: mentionsStats.map(m => m.mention_name),
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: mentionsStats.map(m => m.student_count),
                backgroundColor: colors.map((color, index) => colors[index % colors.length]),
                barThickness: 30 // Barres plus fines
            }]
        };

        // Graphique 2 : Nombre de fichiers soumis par mention
        const dataFiles = {
            labels: mentionsStats.map(m => m.mention_name),
            datasets: [{
                label: 'Nombre de fichiers soumis',
                data: mentionsStats.map(m => m.file_count),
                backgroundColor: colors.map((color, index) => colors[index % colors.length]),
                barThickness: 30 // Barres plus fines
            }]
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
        setChartDataFiles(dataFiles);
        setChartOptions(options);
    }, [mentionsStats]);

    const totalStudents = mentionsStats.reduce((sum, mention) => sum + mention.student_count, 0);

    return (
        <LayoutEnseignant>
            <div className='w-full text-gray-800 p-5 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold mb-10'>Tableau de bord</h1>
                <div className='grid grid-cols-4 w-full items-center justify-center text-white gap-4 mb-4'>
                    <div className='flex flex-col items-center justify-center'>
                        <div className='flex items-center justify-center space-x-5 w-full bg-green-500 py-3 px-10 rounded-t-md'>
                            <div className='flex flex-col space-y-2'>
                                <p className='text-4xl font-extrabold'>{mentionsCount}</p>
                                <p className='text-xl font-semibold'>Mes mentions</p>
                            </div>
                            <MdOutlineLibraryBooks size={60} />
                        </div>
                        <Link className='flex items-center justify-center w-full bg-green-600 p-2 rounded-b-md hover:bg-green-700' to="/enseignant/mention">
                            <p className='font-semibold'>en savoir plus</p>
                            <FaCircleArrowRight className='ml-2' />
                        </Link>
                    </div>
                    <div className='flex flex-col items-center justify-center'>
                        <div className='flex items-center justify-center space-x-5 w-full bg-yellow-600 py-3 px-10 rounded-t-md'>
                            <div className='flex flex-col space-y-2'>
                                <p className='text-4xl font-extrabold'>{totalStudents}</p>
                                <p className='text-xl font-semibold'>Mes étudiants</p>
                            </div>
                            <PiStudent size={60} />
                        </div>
                        <Link className='flex items-center justify-center w-full bg-yellow-700 p-2 rounded-b-md hover:bg-yellow-800' to="/enseignant/etudiant">
                            <p className='font-semibold'>en savoir plus</p>
                            <FaCircleArrowRight className='ml-2' />
                        </Link>
                    </div>
                </div>
                <div className='flex flex-col items-center justify-center w-full px-5 space-y-5 mb-5'>
                    <div className='w-full max-w-4xl p-2'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Répartition des étudiants par mention</h1>
                        <Chart type="bar" data={chartDataStudent} options={chartOptions} />
                    </div>
                    <div className='w-full max-w-4xl p-2'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Fichiers soumis par mention</h1>
                        <Chart type="bar" data={chartDataFiles} options={chartOptions} />
                    </div>
                </div>
            </div>
        </LayoutEnseignant>
    );
};

export default Dashboard;