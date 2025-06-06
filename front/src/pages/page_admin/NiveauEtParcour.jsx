import React, { useState, useEffect } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link, useParams } from 'react-router-dom';
import { getAdminNiveaux } from '../../Services/adminAuthService';
import { Divider } from 'primereact/divider';
import { FaRegEye } from 'react-icons/fa6';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

const NiveauEtParcour = () => {
    const { mentionId } = useParams();
    const [niveaux, setNiveaux] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchNiveaux = async () => {
            try {
                const data = await getAdminNiveaux(mentionId);
                setNiveaux(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };
        fetchNiveaux();
    }, [mentionId]);

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
                    <p className="text-xl font-bold">Erreur lors du chargement des données</p>
                    <p className="text-lg font-semibold">{error}</p>
                </div>
            </LayoutAdmin>
        );
    }

    return (
        <LayoutAdmin>
            <div className='w-full text-gray-800 overflow-x-hidden custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='p-5 text-3xl font-semibold'>Niveau et Parcours</h1>
                <div className='grid grid-cols-1 md:grid-cols-3 gap-5 p-5 items-center justify-center'>
                    {niveaux.map((niveau) => (
                        <div key={niveau.id} className='bg-white shadow-md rounded-lg p-5 w-[400px] border-[1px]'>
                            <h1 className='text-xl font-bold mb-2'>Niveau {niveau.nom}</h1>
                            <Divider />
                            <p className='text-sm font-semibold text-gray-700 mb-6'>{niveau.parcours.join(', ')}</p>
                            <Divider />
                            <div className='flex justify-center'>
                                <Link
                                    to={`/admin/mentions/${mentionId}/niveaux/${niveau.id}/semestres/${niveau.premierSemestreId}/cours`}
                                    className='flex items-center justify-center space-x-1 text-white text-sm bg-[#39B54A] px-2 py-1 rounded-lg hover:scale-105 duration-500 hover:bg-[#257630]'
                                >
                                    <FaRegEye />
                                    <p>Voir les cours</p>
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </LayoutAdmin>
    );
};

export default NiveauEtParcour;