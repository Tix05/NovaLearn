import React, { useState } from 'react';
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
import { mentions } from '../../../public/constants/data';

const DescriptionCours = () => {
    const { mentionId, semestreId, coursId } = useParams();

    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestre = mention?.semestres.find((s) => s.id === semestreId);
    const cours = semestre?.cours.find((c) => c.id === parseInt(coursId));

    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleDownload = (filename) => {
        const link = document.createElement('a');
        link.href = `/path/to/supports/${filename}`;
        link.download = filename;
        link.click();
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-end items-center">
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <Button icon="pi pi-download" rounded severity="secondary" onClick={() => handleDownload(rowData.nom)} />
        );
    };

    const header = renderHeader();

    return (
        <Layout>
            <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-3xl font-normal p-3'>Détails du cours - {cours?.titre}</h1>
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>{cours?.titre}</h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <p className='font-semibold leading-relaxed text-justify'>{cours?.description}</p>
                        </div>
                    </div>
                </div>
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <IoIosDocument className='text-2xl' />
                        <h1>Support Document</h1>
                    </div>
                    <div>
                        <DataTable value={cours?.supports.filter((s) => s.type === 'document')} paginator rows={5} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucun document trouvé.">
                            <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                            <Column field="nom" header="Document" sortable style={{ minWidth: '5rem' }} />
                            <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                        </DataTable>
                    </div>
                </div>
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileAudio className='text-2xl' />
                        <h1>Support Audio</h1>
                    </div>
                    <div>
                        <DataTable value={cours?.supports.filter((s) => s.type === 'audio')} paginator rows={5} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucun audio trouvé.">
                            <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                            <Column field="nom" header="Audio" sortable style={{ minWidth: '5rem' }} />
                            <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                        </DataTable>
                    </div>
                </div>
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileVideo className='text-2xl' />
                        <h1>Support Vidéo</h1>
                    </div>
                    <div>
                        <DataTable value={cours?.supports.filter((s) => s.type === 'video')} paginator rows={5} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucun video trouvé.">
                            <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                            <Column field="nom" header="Video" sortable style={{ minWidth: '5rem' }} />
                            <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                        </DataTable>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default DescriptionCours;