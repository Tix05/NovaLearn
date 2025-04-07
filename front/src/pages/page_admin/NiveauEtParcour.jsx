import React from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link } from 'react-router-dom';
import { mentions } from '../../../public/constants/data';
import { useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import { FaRegEye } from 'react-icons/fa6';

const NiveauEtParcour = () => {
    const { mentionId } = useParams();
    const mention = mentions.find(m => m.id === parseInt(mentionId));

    return (
        <LayoutAdmin>
            <div className='w-full text-gray-800 overflow-x-hidden custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='p-5 text-3xl font-semibold'>Niveau et Parcours</h1>

                <div className='grid grid-cols-1 md:grid-cols-3 gap-5 p-5 items-center justify-center'>
                    {mention?.niveaux?.map((niveau) => {
                        const premierSemestre = niveau?.semestres?.[0]?.id;

                        return (
                            <div key={niveau.id} className='bg-white shadow-md rounded-lg p-5 w-[400px] border-[1px]'>
                                <h1 className='text-xl font-bold mb-2'>Niveau {niveau.nom}</h1>
                                <Divider />
                                <p className='text-sm font-semibold text-gray-700 mb-6'>{niveau.parcours}</p>
                                <Divider />
                                <div className='flex justify-center'>
                                    <Link to={`/admin/mentions/${mentionId}/niveaux/${niveau.id}/semestres/${premierSemestre}/cours`} className='flex items-center justify-center space-x-1 text-white text-sm bg-[#39B54A] px-2 py-1 rounded-lg hover:scale-105 duration-500 hover:bg-[#257630]'>
                                        <FaRegEye />
                                        <p>Voir les cours</p>
                                    </Link>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </LayoutAdmin>
    );
};

export default NiveauEtParcour;