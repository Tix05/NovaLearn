import React, { useState } from 'react';
import { useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import { mentions } from '../../../public/constants/data2';
import Layout from '../../components/Layout';
import AccordionUE from '../../components/AccordionUEEtudiant';

export default function Cours() {
    const { mentionId, niveauId } = useParams();
    const [selectedSemestre, setSelectedSemestre] = useState(null);


    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestres = mention?.semestres || [];

    useEffect(() => {
        if (semestres.length > 0) {
            setSelectedSemestre(semestres[0].id);
        }
    }, [semestres]);

    const semestre = semestres.find((s) => s.id === selectedSemestre);
    const ues = semestre?.ues || [];


    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl font-semibold text-gray-800 p-5 '>{mention?.nom} - {mention?.niveau}</h1>
                <div className="flex items-center py-4 space-x-5 p-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/etudiant/cours/${mentionId}/${s.id}`}
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
                    <h1 className='text-lg font-semibold text-gray-800'>Parcours : {mention?.parcours}</h1>
                </div>
            </div>
        );
    };

    return (
        <Layout>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                {renderHeader()}
                <AccordionUE
                    ues={ues}
                    mentionId={mentionId}
                    niveauId={niveauId}
                    semestreId={selectedSemestre}
                />
            </div>
        </Layout>
    );
}
