// CoursAdmin.js
import React, { useState, useEffect } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link, useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import { mentions } from '../../../public/constants/data';
import AccordionUE from '../../components/AccordionUE';

export default function CoursAdmin() {
    const { mentionId, niveauId, semestreId } = useParams();
    const [selectedSemestre, setSelectedSemestre] = useState(null);

    // Trouver la mention, le niveau et le semestre correspondant
    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const niveau = mention?.niveaux?.find((n) => n.id === niveauId);
    const semestres = niveau?.semestres || [];

    useEffect(() => {
        if (semestres.length > 0 && !semestreId) {
            setSelectedSemestre(semestres[0].id);
        } else if (semestreId) {
            setSelectedSemestre(semestreId);
        }
    }, [semestres, semestreId]);

    const semestre = semestres.find((s) => s.id === selectedSemestre);
    const ues = semestre?.ues || [];

    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl text-gray-800 font-semibold p-5'>{mention?.nom} - {niveau?.nom}</h1>
                <div className="flex items-center py-4 space-x-5 p-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${s.id}/cours`}
                            className={`pb-2 transition duration-400 ${selectedSemestre === s.id
                                ? "border-b-2 border-blue-500 text-blue-600 font-semibold"
                                : "text-gray-600 hover:text-blue-500"
                                }`}
                            onClick={() => setSelectedSemestre(s.id)}
                        >
                            {s.intitule}
                        </Link>
                    ))}
                </div>
                <Divider />
                <div className='flex justify-between items-center p-3'>
                    <h1 className='text-lg font-semibold text-gray-800'>Parcours : {niveau?.parcours}</h1>
                </div>
            </div>
        );
    };

    return (
        <LayoutAdmin>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                {renderHeader()}
                <AccordionUE
                    ues={ues}
                    mentionId={mentionId}
                    niveauId={niveauId}
                    semestreId={selectedSemestre}
                />
            </div>
        </LayoutAdmin>
    );
}