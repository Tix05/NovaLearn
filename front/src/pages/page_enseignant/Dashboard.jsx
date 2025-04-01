import React, { useState } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Calendar } from 'primereact/calendar';
import Weather from '../../components/Weather';
import { FaCircleArrowRight } from "react-icons/fa6";
import { BiSolidMessageRounded } from "react-icons/bi";
import { Link } from 'react-router-dom';


const Dashboard = () => {
    const [date, setDate] = useState(null);

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
                <div className="flex justify-center items-center gap-x-10">
                    <Calendar value={date} onChange={(e) => setDate(e.value)} inline />
                    <Weather />
                </div>
            </div>
        </LayoutEnseignant>
    );
};

export default Dashboard;