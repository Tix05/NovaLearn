import React, { useState, useEffect } from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { FaFileAudio, FaFileVideo } from 'react-icons/fa6';
import { useParams } from 'react-router-dom';
import { getStudentMentions } from '../../Services/authService';

const DescriptionCours = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentions, setMentions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getStudentMentions();
                setMentions(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    // Trouver le cours dans la structure
    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestre = mention?.semestres.find((s) => s.id === semestreId);
    let cours = null;

    if (semestre) {
        for (const ue of semestre.ues) {
            const foundCours = ue.cours.find((c) => c.id === parseInt(coursId));
            if (foundCours) {
                cours = foundCours;
                break;
            }
        }
    }

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleDownload = (url) => {
        if (!url) return;
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-end items-center">
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={globalFilterValue}
                        onChange={onGlobalFilterChange}
                        placeholder="Rechercher..."
                        className='custom-input'
                    />
                </IconField>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <Button
                icon="pi pi-download"
                rounded
                severity="secondary"
                onClick={() => handleDownload(rowData.url)}
                disabled={!rowData.url}
            />
        );
    };

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

    if (error) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className='text-xl font-bold'>Erreur lors du chargement des données</p>
                    <p className='text-lg font-semibold'>{error}</p>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-3xl font-normal p-3'>
                    {cours ? `Détails du cours - ${cours.titre}` : 'Détails du cours'}
                </h1>

                {/* Section Description - Toujours affichée */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>
                        {cours?.titre || 'Titre non disponible'}
                    </h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <p className='font-semibold leading-relaxed text-justify'>
                                {cours?.description || 'Aucune description disponible'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Section Documents - Toujours affichée */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <IoIosDocument className='text-2xl mr-2' />
                        <h1>Support Document</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'document') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Audio - Toujours affichée */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileAudio className='text-2xl mr-2' />
                        <h1>Support Audio</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'audio') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Vidéo - Toujours affichée */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileVideo className='text-2xl mr-2' />
                        <h1>Support Vidéo</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'video') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default DescriptionCours;