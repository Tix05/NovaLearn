import React, { useState, useRef } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { TabView, TabPanel } from 'primereact/tabview';
import { InputText } from "primereact/inputtext";
import { FileUpload } from 'primereact/fileupload';
import { Button } from 'primereact/button';
import { Toast } from 'primereact/toast';
import { useParams } from 'react-router-dom';
import { IoIosDocument } from 'react-icons/io';
import { FaFileAudio, FaFileVideo } from 'react-icons/fa';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { mentions } from '../../../public/constants/data';

const AjoutSupportAdmin = () => {
    const { mentionId, semestreId, coursId, niveauId } = useParams();
    const [activeIndex, setActiveIndex] = useState(0);
    const [titre, setTitre] = useState('');
    const [selectedFiles, setSelectedFiles] = useState({});
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const toast = useRef(null);
    const fileUploadRefs = useRef({});

    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const niveau = mention?.niveaux?.find((n) => n.id === niveauId);
    const semestre = niveau?.semestres?.find((s) => s.id === semestreId);


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
    const supportTypes = [
        {
            name: 'document',
            icon: <IoIosDocument className='text-2xl' />,
            accept: '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx',
            label: 'Document'
        },
        {
            name: 'video',
            icon: <FaFileVideo className='text-2xl' />,
            accept: 'video/*',
            label: 'Vidéo'
        },
        {
            name: 'audio',
            icon: <FaFileAudio className='text-2xl' />,
            accept: 'audio/*',
            label: 'Audio'
        }
    ];

    const handleFileSelect = (e, type) => {
        setSelectedFiles(prev => ({
            ...prev,
            [type]: e.files[0] // Stocke seulement le premier fichier
        }));
    };

    const handleCancelUpload = (type) => {
        setSelectedFiles(prev => {
            const newFiles = { ...prev };
            delete newFiles[type];
            return newFiles;
        });

        // Réinitialise le composant FileUpload
        if (fileUploadRefs.current[type]) {
            fileUploadRefs.current[type].clear();
        }
    };

    const handleDelete = (support) => {
        // Implémentez la suppression ici
        showToast('success', 'Succès', 'Support supprimé avec succès');
    };

    const handleSubmit = () => {
        const currentType = supportTypes[activeIndex].name;

        if (!titre || !selectedFiles[currentType]) {
            showToast('warn', 'Attention', 'Veuillez remplir tous les champs et sélectionner un fichier');
            return;
        }

        // Simulation d'envoi avec date actuelle
        const newSupport = {
            id: Math.random(),
            titre,
            nom: selectedFiles[currentType].name,
            type: currentType,
            date: new Date().toLocaleDateString('fr-FR')
        };

        showToast('success', 'Succès', 'Support ajouté avec succès');
        setTitre('');
        handleCancelUpload(currentType);
    };

    const showToast = (severity, summary, detail) => {
        toast.current.show({
            severity,
            summary,
            detail,
            life: 3000,
            position: 'bottom-right'
        });
    };

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
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

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="bottom-right" className="mb-5 mr-5" />

            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-2xl font-semibold text-gray-800 p-3'>Ajouter un support pour le cours {cours?.titre}</h1>

                <TabView
                    activeIndex={activeIndex}
                    onTabChange={(e) => setActiveIndex(e.index)}
                    className='custom-tabview'
                >
                    {supportTypes.map((type) => {
                        const supports = cours?.supports?.filter((s) => s.type === type.name) || [];
                        const hasSelectedFile = !!selectedFiles[type.name];

                        return (
                            <TabPanel
                                key={type.name}
                                header={`Support ${type.label}`}
                                className='flex flex-col items-center space-y-10'
                            >
                                <form className="flex flex-col w-full">
                                    <div className='flex flex-col items-center w-full bg-white shadow-lg rounded-lg border-[1px] border-gray-300 p-5'>
                                        <div className='flex flex-col md:flex-row w-full gap-5 mb-5'>
                                            <div className='flex items-center gap-5 flex-1'>
                                                <span className='font-medium text-nowrap'>Titre :</span>
                                                <InputText
                                                    value={titre}
                                                    onChange={(e) => setTitre(e.target.value)}
                                                    className='input-focus w-full'
                                                    placeholder={`Titre du ${type.label}`}
                                                />
                                            </div>
                                            <div className='flex items-center gap-3 flex-1'>
                                                <span className='font-medium'>Fichier :</span>
                                                <div className="flex items-center gap-2">
                                                    {hasSelectedFile ? (
                                                        <div className="flex items-center gap-2">
                                                            <span>{selectedFiles[type.name].name}</span>
                                                            <Button
                                                                icon="pi pi-times"
                                                                className="p-button-rounded p-button-text p-button-danger"
                                                                onClick={() => handleCancelUpload(type.name)}
                                                            />
                                                        </div>
                                                    ) : (
                                                        <FileUpload
                                                            ref={(el) => fileUploadRefs.current[type.name] = el}
                                                            mode="basic"
                                                            name={`file_${type.name}`}
                                                            accept={type.accept}
                                                            maxFileSize={10000000}
                                                            chooseLabel="Sélectionner un fichier"
                                                            onSelect={(e) => handleFileSelect(e, type.name)}
                                                            auto
                                                            customUpload
                                                            className='custom-fileupload-button'
                                                        />
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex gap-3 justify-end w-full">
                                            <Button
                                                label="Annuler"
                                                icon="pi pi-times"
                                                severity="secondary"
                                                onClick={() => handleCancelUpload(type.name)}
                                                disabled={!hasSelectedFile}
                                            />
                                            <Button
                                                label="Enregistrer"
                                                icon="pi pi-save"
                                                onClick={handleSubmit}
                                                disabled={!titre || !hasSelectedFile}
                                            />
                                        </div>
                                    </div>
                                </form>

                                <div className='flex flex-col shadow-md w-full border-[1px] rounded-lg'>
                                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center gap-2'>
                                        {type.icon}
                                        <h1>Supports {type.label}</h1>
                                    </div>
                                    <div className='p-3'>
                                        <DataTable
                                            value={supports}
                                            paginator
                                            rows={5}
                                            dataKey="id"
                                            globalFilter={globalFilterValue}
                                            header={renderHeader(type.label)}
                                            emptyMessage={`Aucun ${type.label} trouvé`}
                                            className='p-datatable-sm'
                                        >
                                            <Column
                                                field="titre"
                                                header="Titre"
                                                sortable
                                                style={{ minWidth: '10rem' }}
                                            />
                                            <Column
                                                field="nom"
                                                header="Fichier"
                                                sortable
                                                style={{ minWidth: '5rem' }}
                                            />
                                            <Column
                                                field="date"
                                                header="Date d'ajout"
                                                sortable
                                                style={{ minWidth: '8rem' }}
                                            />
                                        </DataTable>
                                    </div>
                                </div>
                            </TabPanel>
                        );
                    })}
                </TabView>
            </div>
        </LayoutAdmin>
    );
};

export default AjoutSupportAdmin;