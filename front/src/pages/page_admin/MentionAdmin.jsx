import React from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link } from 'react-router-dom';
import { mentions } from '../../../public/constants/data';

const MentionAdmin = () => {
    return (
        <LayoutAdmin>
            <div className='w-full text-gray-800 overflow-x-hidden custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='p-5 text-3xl font-semibold'>Toutes les mentions</h1>
                <div className='grid grid-cols-1 gap-5 p-5 m-5 items-center justify-center md:grid-cols-2 lg:grid-cols-3 h-sc'>
                    {mentions.map((mention) => (
                        <Link
                            key={mention.id}
                            to={`/admin/mentions/${mention.id}/niveaux`}
                            className='w-60 bg-white shadow-md rounded-lg border-[1px] p-5 flex flex-col items-center hover:shadow-lg hover:scale-105 transition duration-300'
                        >
                            <img src={mention.icon} alt="icon" className='w-16 h-16 mb-3' />
                            <h2 className='text-lg font-semibold text-center'>{mention.nom}</h2>
                        </Link>
                    ))}
                </div>
            </div>
        </LayoutAdmin>
    );
};

export default MentionAdmin;