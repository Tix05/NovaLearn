// src/pages/Agenda.js

import React, { useState, useEffect } from 'react';
import { TabView, TabPanel } from 'primereact/tabview';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';
import { getStudentAgenda } from '../../Services/agendaService';

const Agenda = () => {
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const [agendaData, setAgendaData] = useState({
        cours: [],
        examens: [],
        evenements: []
    });
    const [loading, setLoading] = useState(true);

    const periodeOptions = [
        { label: 'Tout', value: 'Tout' },
        { label: 'Semaine', value: 'Semaine' },
        { label: 'Mois', value: 'Mois' },
        { label: 'Trimestre', value: 'Trimestre' },
        { label: 'Semestre', value: 'Semestre' },
    ];

    useEffect(() => {
        const fetchAgendaData = async () => {
            try {
                const data = await getStudentAgenda();
                setAgendaData(data);
                setLoading(false);
            } catch (error) {
                console.error('Error loading agenda:', error);
                setLoading(false);
            }
        };

        fetchAgendaData();
    }, []);

    const filterData = (data) => {
        if (periodeFilter === 'Tout') {
            return data;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const endDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine':
                endDate.setDate(today.getDate() + 7);
                break;
            case 'Mois':
                endDate.setMonth(today.getMonth() + 1);
                break;
            case 'Trimestre':
                endDate.setMonth(today.getMonth() + 3);
                break;
            case 'Semestre':
                endDate.setMonth(today.getMonth() + 6);
                break;
            default:
                return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            itemDate.setHours(0, 0, 0, 0);
            return itemDate >= today && itemDate <= endDate;
        });
    };

    const renderItem = (item) => (
        <div
            key={item.id}
            className="flex flex-col md:flex-row border-[1px] mb-4 w-full md:w-4/5 rounded-lg bg-white shadow-md"
        >
            <div className="flex flex-col p-4 text-center md:w-28 flex-shrink-0">
                <h1 className="text-5xl font-bold text-gray-800">{new Date(item.date).getDate()}</h1>
                <h2 className="text-lg font-semibold text-gray-700 uppercase">
                    {new Date(item.date).toLocaleString('default', { month: 'short' })}
                </h2>
                <h3 className="text-sm text-gray-500">
                    {new Date(item.date).toLocaleString('default', { weekday: 'short' })}
                </h3>
            </div>

            <Divider layout="vertical" className="hidden md:block h-auto" />

            <div className="flex flex-col p-4 flex-1 min-h-[180px]">
                <div className="flex justify-between items-start mb-2">
                    <h1 className="text-xl font-semibold text-gray-800 truncate flex-1">
                        {item.titre}
                    </h1>
                    <span className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded ml-2">
                        {item.type}
                    </span>
                </div>

                <div className="mb-3">
                    <p className="text-gray-600 whitespace-pre-wrap break-words">
                        {item.description}
                    </p>
                    {item.mention && item.parcours && item.niveau && (
                        <p className="text-sm text-gray-500 mt-1">
                            {item.mention} - {item.parcours} - {item.niveau}
                        </p>
                    )}
                </div>

                {item.image && (
                    <div className="mt-3">
                        <img
                            src={item.image}
                            alt={item.titre}
                            className="w-full h-auto rounded-lg border border-gray-200"
                        />
                    </div>
                )}

                {item.video && (
                    <div className="mt-3">
                        <div className="relative pt-[56.25%] bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                            <video
                                controls
                                className="absolute inset-0 w-full h-full"
                            >
                                <source src={item.video} type="video/mp4" />
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        </div>
                    </div>
                )}

                <div className="mt-3 pt-2 border-t border-gray-100">
                    <p className="text-sm font-medium text-gray-700">
                        Publié par: {item.nom_auteur}
                    </p>
                </div>
            </div>
        </div>
    );

    const renderFilterSection = () => (
        <div className="mb-6 flex gap-2 w-60">
            <Dropdown
                value={periodeFilter}
                onChange={(e) => setPeriodeFilter(e.value)}
                options={periodeOptions}
                optionLabel="label"
                placeholder="Période"
                panelClassName="font-poppins text-sm"
                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
            />
        </div>
    );

    if (loading) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex items-center justify-center">
                    <div class="spinner-container">
                        <div class="spinner-outer">
                            <div class="spinner-inner"></div>
                        </div>
                    </div>
                </div>
            </Layout>
        );
    }


    return (
        <Layout>
            <div className="card custom-scrollbar h-[90vh] overflow-y-auto">
                <TabView className='custom-tabview'>
                    <TabPanel header="Cours" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.cours).length > 0 ? (
                                filterData(agendaData.cours).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun cours prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Examens" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.examens).length > 0 ? (
                                filterData(agendaData.examens).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun examen prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Evènements" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.evenements).length > 0 ? (
                                filterData(agendaData.evenements).map(renderItem)
                            ) : (
                                <p className="text-gray-500">Aucun évènement prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                </TabView>
            </div>
        </Layout>
    );
};

export default Agenda;