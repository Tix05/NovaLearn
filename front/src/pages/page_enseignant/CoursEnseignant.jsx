import React, { useState, useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import AccordionUE from '../../components/AccordionUEEnseignant';
import { getTeacherMentions } from '../../Services/teacherAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

export default function CoursEnseignant() {
    const { mentionId } = useParams();
    const [selectedSemestre, setSelectedSemestre] = useState(null);
    const [mentions, setMentions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getTeacherMentions();
                setMentions(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestres = mention?.semestres || [];

    useEffect(() => {
        if (semestres.length > 0) {
            setSelectedSemestre(semestres[0].id);
        }
    }, [semestres]);

    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl font-semibold text-gray-800 p-5'>
                    {mention?.nom} - {mention?.niveau}
                </h1>
                <div className="flex items-center py-4 space-x-5 p-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/enseignant/coursEnseignant/${mentionId}/${s.id}`}
                            className={`pb-2 transition duration-400 ${selectedSemestre === s.id
                                ? "border-b-2 border-blue-500 text-blue-600 font-semibold"
                                : "text-gray-600 hover:text-blue-500 font-semibold"
                                }`}
                            onClick={() => setSelectedSemestre(s.id)}
                        >
                            {s.intitule}
                        </Link>
                    ))}
                </div>
                <Divider />
                <div className='flex justify-between items-center p-3'>
                    <h1 className='text-lg font-semibold text-gray-800'>
                        Parcours : {mention?.parcours}
                    </h1>
                </div>
            </div>
        );
    };

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
                    <p className='text-xl font-bold'>Erreur lors du chargement des données</p>
                    <p className='text-lg font-semibold'>{error}</p>
                </div>
            </LayoutEnseignant>
        );
    }

    if (!mention) {
        return (
            <LayoutEnseignant>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className='text-xl font-bold'>Aucune mention trouvée avec cet ID</p>
                </div>
            </LayoutEnseignant>
        );
    }

    const semestre = semestres.find((s) => s.id === selectedSemestre);
    const ues = semestre?.ues || [];

    return (
        <LayoutEnseignant>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                {renderHeader()}
                <AccordionUE
                    ues={ues}
                    mentionId={mentionId}
                    semestreId={selectedSemestre}
                />
            </div>
        </LayoutEnseignant>
    );
}