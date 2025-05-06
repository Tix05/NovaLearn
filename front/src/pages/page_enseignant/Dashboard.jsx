import React, { useState, useEffect } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Calendar } from 'primereact/calendar';
// import Weather from '../../components/Weather';
import { FaCircleArrowRight } from "react-icons/fa6";
import { BiSolidMessageRounded } from "react-icons/bi";
import { Link } from 'react-router-dom';
import { Chart } from 'primereact/chart';

const Dashboard = () => {
    const [date, setDate] = useState(null);
    const [chartDataStudent, setChartDataStudent] = useState({});
    const [chartOptionsStudent, setChartOptionsStudent] = useState({});

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

    return (
        <LayoutEnseignant>
            <div className=' w-full text-gray-800 p-5 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold mb-10'>Tableau de bord</h1>
                <div className='grid grid-cols-4 w-full items-center justify-center text-white gap-4 mb-4'>
                    <div className='flex flex-col items-center justify-center'>
                        <div className='flex items-center justify-center space-x-5 w-full bg-cyan-500 py-3 px-10 rounded-t-md'>
                            <div className='flex flex-col space-y-2'>
                                <p className='text-4xl font-extrabold'>2</p>
                                <p className='text-xl font-semibold'>Commentaires</p>
                            </div>
                            <BiSolidMessageRounded size={60} />
                        </div>
                        <Link className='flex items-center justify-center w-full bg-cyan-600 p-2 rounded-b-md hover:bg-cyan-700' to="#" >
                            <p className='font-semibold'>en savoir plus</p>
                            <FaCircleArrowRight className='ml-2' />
                        </Link>
                    </div>
                    <div className='flex flex-col items-center justify-center'>
                        <div className='flex items-center justify-center space-x-5 w-full bg-green-500 py-3 px-10 rounded-t-md'>
                            <div className='flex flex-col space-y-2'>
                                <p className='text-4xl font-extrabold'>5</p>
                                <p className='text-xl font-semibold'>Mes mentions</p>
                            </div>
                            <BiSolidMessageRounded size={60} />
                        </div>
                        <Link className='flex items-center justify-center w-full bg-green-600 p-2 rounded-b-md hover:bg-green-700' to="#">
                            <p className='font-semibold'>en savoir plus</p>
                            <FaCircleArrowRight className='ml-2' />
                        </Link>
                    </div>
                    <div className='flex flex-col items-center justify-center'>
                        <div className='flex items-center justify-center space-x-5 w-full bg-yellow-600 py-3 px-10 rounded-t-md'>
                            <div className='flex flex-col space-y-2'>
                                <p className='text-4xl font-extrabold'>107</p>
                                <p className='text-xl font-semibold'>Mes étudiants</p>
                            </div>
                            <BiSolidMessageRounded size={60} />
                        </div>
                        <Link className='flex items-center justify-center w-full bg-yellow-700 p-2 rounded-b-md hover:bg-yellow-800' to="#">
                            <p className='font-semibold'>en savoir plus</p>
                            <FaCircleArrowRight className='ml-2' />
                        </Link>
                    </div>
                </div>
                <div className='flex flex-col items-center justify-center w-full px-5 space-y-5 mb-5'>
                    <div className='w-full p-2 bg-white border-[1px] shadow-md rounded-lg'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des étudiants</h1>
                        <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                    </div>
                    <div className='w-full p-2 bg-white border-[1px] shadow-md rounded-lg'>
                        <h1 className='text-xl py-3 font-bold text-gray-700 text-center'>Evolution des enseignants</h1>
                        <Chart type="line" data={chartDataStudent} options={chartOptionsStudent} />
                    </div>
                </div>
            </div>
        </LayoutEnseignant>
    );
};

export default Dashboard;