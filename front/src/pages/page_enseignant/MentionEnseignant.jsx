import React, { useState, useEffect } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Divider } from 'primereact/divider';
import { Link } from 'react-router-dom';
import { FaRegEye } from 'react-icons/fa';
import { getTeacherMentions } from '../../Services/teacherAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

const MentionEnseignant = () => {
    const [mentions, setMentions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getTeacherMentions();
                // Si parcours est un tableau, créer une entrée par parcours
                const expandedMentions = data.flatMap((mention) => {
                    const parcours = Array.isArray(mention.parcours)
                        ? mention.parcours
                        : [mention.parcours || 'Non spécifié'];
                    return parcours.map((parcoursItem) => ({
                        ...mention,
                        parcours: parcoursItem
                    }));
                });
                setMentions(expandedMentions);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    if (loading) {
        return (
            <LayoutEnseignant>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </LayoutEnseignant>
        );
    }

    if (error) {
        return (
            <LayoutEnseignant>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className="text-xl font-bold">Erreur lors du chargement des données</p>
                    <p className="text-lg font-semibold">{error}</p>
                </div>
            </LayoutEnseignant>
        );
    }

    if (!mentions.length) {
        return (
            <LayoutEnseignant>
                <div className="h-[90vh] w-full flex flex-col text-gray-800 items-center space-y-5 justify-center">
                    <p className="text-xl font-bold">Aucune mention trouvée</p>
                </div>
            </LayoutEnseignant>
        );
    }

    return (
        <LayoutEnseignant>
            <div
                className="w-full text-gray-800 overflow-x-hidden custom-scrollbar"
                style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}
            >
                <h1 className="p-4 text-3xl font-semibold text-gray-700">Mes mentions</h1>
                <div className="grid grid-cols-2 gap-5 p-5 justify-center">
                    {mentions.map((mention, index) => (
                        <div
                            key={`${mention.id}-${index}`}
                            className="bg-white shadow-md rounded-lg border-[1px] w-[300px] flex flex-col"
                        >
                            <div className="flex items-center p-3 space-x-5">
                                <img
                                    src={mention.icon || '/default-icon.png'}
                                    alt="icon-mention"
                                    className="w-[30px]"
                                    onError={(e) => (e.target.src = '/default-icon.png')}
                                />
                                <h1 className="font-bold text-md">{mention.nom}</h1>
                            </div>
                            <Divider />
                            <div className="flex flex-col p-3 text-lg font-semibold flex-grow">
                                <p>Niveau : {mention.niveau}</p>
                                <p>Parcours : {mention.parcours || 'Non spécifié'}</p>
                                <Divider />
                                <Link
                                    to={`/enseignant/coursEnseignant/${mention.id}`}
                                    className="flex mx-16 items-center justify-center text-white text-sm bg-emerald-600 px-2 py-1 rounded-lg hover:scale-105 duration-500 hover:bg-[#257630]"
                                >
                                    <FaRegEye className="mr-1" />
                                    <p>Voir les cours</p>
                                </Link>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </LayoutEnseignant>
    );
};

export default MentionEnseignant;
