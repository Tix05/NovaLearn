import React, { useState, useRef, useEffect } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { FaFileAudio, FaFileVideo, FaTrash, FaDownload } from 'react-icons/fa6';
import { useParams } from 'react-router-dom';
import { mentions } from '../../../public/constants/data2';
import { Toast } from 'primereact/toast';

const DescriptionCoursEnseignant = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [description, setDescription] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const toast = useRef(null);

    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestre = mention?.semestres.find((s) => s.id === semestreId);
    const cours = semestre?.cours.find((c) => c.id === parseInt(coursId));

    useEffect(() => {
        if (cours?.description) {
            setDescription(cours.description);
        }
    }, [cours]);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleDownload = (filename) => {
        showToast('success', 'Succès', 'Téléchargement commencé');
    };

    const handleDelete = (support) => {
        showToast('success', 'Succès', 'Support supprimé avec succès');
    };

    const handleSaveDescription = () => {
        setIsSaving(true);
        setTimeout(() => {
            setIsSaving(false);
            showToast('success', 'Succès', 'Description enregistrée');
        }, 500);
    };

    const showToast = (severity, summary, detail) => {
        toast.current.show({
            severity,
            summary,
            detail,
            life: 3000,
        });
    };

    const renderHeader = (type) => {
        return (
            <div className="flex justify-between items-center">
                <span className="text-xl font-bold">Supports {type}</span>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={globalFilterValue}
                        onChange={onGlobalFilterChange}
                        placeholder="Rechercher..."
                    />
                </IconField>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className="flex gap-2">
                <Button
                    icon={<FaDownload />}
                    rounded
                    severity="info"
                    onClick={() => handleDownload(rowData.nom)}
                    tooltip="Télécharger"
                    tooltipOptions={{ position: 'top' }}
                />
                <Button
                    icon={<FaTrash />}
                    rounded
                    severity="danger"
                    onClick={() => handleDelete(rowData)}
                    tooltip="Supprimer"
                    tooltipOptions={{ position: 'top' }}
                />
            </div>
        );
    };

    return (
        <LayoutEnseignant>
            <Toast
                ref={toast}
                position='bottom-right'
            />
            <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-3xl font-normal p-3'>Détails du cours {cours?.titre}</h1>

                {/* Section description */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>{cours?.titre}</h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <textarea
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                className="w-full border border-gray-300 rounded-lg p-3 font-semibold text-gray-800 focus:outline-none"
                                rows="5"
                                placeholder="Ajouter ou modifier la description ici..."
                            />
                        </div>
                        <div className='flex justify-end'>
                            <Button
                                label={isSaving ? "Enregistrement..." : "Enregistrer"}
                                icon="pi pi-save"
                                onClick={handleSaveDescription}
                                disabled={isSaving}
                            />
                        </div>
                    </div>
                </div>

                {/* Sections supports */}
                {['document', 'audio', 'video'].map((type) => {
                    const supports = cours?.supports?.filter((s) => s.type === type) || [];
                    const typeConfig = {
                        document: { icon: <IoIosDocument className='text-2xl' />, label: 'Document' },
                        audio: { icon: <FaFileAudio className='text-2xl' />, label: 'Audio' },
                        video: { icon: <FaFileVideo className='text-2xl' />, label: 'Vidéo' }
                    }[type];

                    return (
                        <div key={type} className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                            <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center gap-2'>
                                {typeConfig.icon}
                                <h1>Support {typeConfig.label}</h1>
                            </div>
                            <div className='p-3'>
                                <DataTable
                                    value={supports}
                                    paginator
                                    rows={5}
                                    dataKey="id"
                                    globalFilter={globalFilterValue}
                                    header={renderHeader(typeConfig.label)}
                                    emptyMessage={`Aucun ${typeConfig.label} trouvé`}
                                >
                                    <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                                    <Column field="nom" header="Fichier" sortable style={{ minWidth: '5rem' }} />
                                    <Column field="date" header="Date d'ajout" sortable style={{ minWidth: '8rem' }} />
                                    <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                                </DataTable>
                            </div>
                        </div>
                    );
                })}
            </div>
        </LayoutEnseignant>
    );
};

export default DescriptionCoursEnseignant;