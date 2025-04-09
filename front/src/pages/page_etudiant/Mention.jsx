import React from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { Link } from 'react-router-dom';
import { FaRegEye } from 'react-icons/fa';
import { mentions } from '../../../public/constants/data2';

const Mention = () => {
    return (
        <Layout>
            <div className='w-full text-gray-800 overflow-x-hidden custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='p-4 md:text-3xl text-xl font-normal'>Mes mentions</h1>
                <div className='grid grid-cols-2 gap-5 p-5 justify-center'>
                    {mentions.map((mention) => (
                        <div key={mention.id} className='bg-white shadow-md rounded-lg border-[1px] w-[500px] flex flex-col'>
                            <div className='flex items-center p-3 space-x-5'>
                                <img src={mention.icon} alt="icon-mention" className='w-[30px]' />
                                <h1 className='font-bold text-md'>{mention.nom}</h1>
                            </div>
                            <Divider />
                            <div className='flex flex-col p-3 text-xs font-semibold flex-grow'>
                                <p>Vous êtes inscris en {mention.niveau}</p>
                                <Divider />
                                <p>Parcours {mention.parcours}</p>
                                <Divider />
                                <p>Matricule</p>
                                <div className='flex p-5 justify-between items-center mt-auto'>
                                    <p>{mention.matricule}</p>
                                    <Link to={`/etudiant/cours/${mention.id}`} className='flex items-center justify-center space-x-1 text-white text-sm bg-[#39B54A] px-2 py-1 rounded-lg hover:scale-105 duration-500 hover:bg-[#257630]'>
                                        <FaRegEye />
                                        <p>Voir les cours</p>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Layout>
    );
};

export default Mention;