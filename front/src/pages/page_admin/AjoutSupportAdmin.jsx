import React, { useState, useRef, useEffect } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { TabView, TabPanel } from 'primereact/tabview';
import { InputText } from "primereact/inputtext";
import { FileUpload } from 'primereact/fileupload';
import { Button } from 'primereact/button';
import { Toast } from 'primereact/toast';
import { ProgressBar } from 'primereact/progressbar';
import { useParams } from 'react-router-dom';
import { IoIosDocument } from 'react-icons/io';
import { FaFileAudio, FaFileVideo, FaLink } from 'react-icons/fa6';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { addAdminSupport, getAdminCoursDetails } from '../../Services/adminAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

const AjoutSupportAdmin = () => {
    const { mentionId, niveauId, semestreId, coursId } = useParams();
    const [activeIndex, setActiveIndex] = useState(0);
    const [titre, setTitre] = useState('');
    const [selectedFiles, setSelectedFiles] = useState({});
    const [linkUrl, setLinkUrl] = useState('');
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [cours, setCours] = useState(null);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const toast = useRef(null);
    const fileUploadRefs = useRef({});

    const validVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    const validAudioTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg'];

    useEffect(() => {
        const fetchCoursDetails = async () => {
            try {
                const data = await getAdminCoursDetails(mentionId, niveauId, semestreId, coursId);
                setCours(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };
        fetchCoursDetails();
    }, [mentionId, niveauId, semestreId, coursId]);

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
            accept: 'video/mp4,video/webm,video/ogg',
            label: 'Vidéo'
        },
        {
            name: 'audio',
            icon: <FaFileAudio className='text-2xl' />,
            accept: 'audio/mpeg,audio/wav,audio/ogg',
            label: 'Audio'
        },
        {
            name: 'lien',
            icon: <FaLink className='text-2xl' />,
            accept: null,
            label: 'Lien'
        }
    ];

    const handleFileSelect = (e, type) => {
        const file = e.files[0];
        if (!file) return;

        if (type === 'video' && !validVideoTypes.includes(file.type)) {
            showToast('error', 'Erreur', 'Format vidéo non supporté. Formats acceptés : MP4, WebM, OGG.');
            return;
        }
        if (type === 'audio' && !validAudioTypes.includes(file.type)) {
            showToast('error', 'Erreur', 'Format audio non supporté. Formats acceptés : MP3, WAV, OGG.');
            return;
        }

        setSelectedFiles(prev => ({
            ...prev,
            [type]: file
        }));
    };

    const handleCancelUpload = (type) => {
        setSelectedFiles(prev => {
            const newFiles = { ...prev };
            delete newFiles[type];
            return newFiles;
        });
        setLinkUrl('');
        if (fileUploadRefs.current[type]) {
            fileUploadRefs.current[type].clear();
        }
    };

    const handleSubmit = async () => {
        const currentType = supportTypes[activeIndex].name;

        if (!titre) {
            showToast('warn', 'Attention', 'Veuillez remplir le champ titre');
            return;
        }

        if (currentType !== 'lien' && !selectedFiles[currentType]) {
            showToast('warn', 'Attention', 'Veuillez sélectionner un fichier');
            return;
        }

        if (currentType === 'lien' && !linkUrl) {
            showToast('warn', 'Attention', 'Veuillez fournir une URL');
            return;
        }

        setIsSubmitting(true);
        setUploadProgress(0);

        try {
            const response = await addAdminSupport(
                coursId,
                titre,
                currentType,
                currentType !== 'lien' ? selectedFiles[currentType] : null,
                selectedFiles[currentType]?.type || null,
                currentType === 'lien' ? linkUrl : null,
                true,
                (progress) => setUploadProgress(progress)
            );

            setCours(prev => ({
                ...prev,
                supports: [
                    ...(prev.supports || []),
                    {
                        id: response.support.id,
                        titre: response.support.titre,
                        type: response.support.type,
                        url: response.support.url,
                        fichier: response.support.fichier,
                        date_ajout: response.support.date_ajout,
                        estPublique: response.support.estPublique
                    }
                ]
            }));

            showToast('success', 'Succès', 'Support ajouté avec succès');
            setTitre('');
            setLinkUrl('');
            handleCancelUpload(currentType);
        } catch (error) {
            showToast('error', 'Erreur', error.message);
        } finally {
            setIsSubmitting(false);
            setUploadProgress(0);
        }
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

    if (!cours) {
        return (
            <LayoutAdmin>
                <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                    <h1 className='text-3xl font-normal p-3'>Cours non trouvé</h1>
                </div>
            </LayoutAdmin>
        );
    }

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="bottom-right" className="mb-5 mr-5" />
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-2xl font-semibold text-gray-800 p-3'>Ajouter un support pour le cours {cours.titre}</h1>
                {isSubmitting && (
                    <div className='p-3'>
                        <ProgressBar value={uploadProgress} style={{ height: '6px' }} />
                    </div>
                )}
                <TabView
                    activeIndex={activeIndex}
                    onTabChange={(e) => setActiveIndex(e.index)}
                    className='custom-tabview'
                >
                    {supportTypes.map((type) => {
                        const supports = cours.supports?.filter((s) => s.type === type.name) || [];
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
                                                    {type.name === 'lien' ? (
                                                        <InputText
                                                            value={linkUrl}
                                                            onChange={(e) => setLinkUrl(e.target.value)}
                                                            placeholder="URL du lien"
                                                            className='input-focus w-full'
                                                        />
                                                    ) : hasSelectedFile ? (
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
                                                disabled={type.name === 'lien' ? !linkUrl : !hasSelectedFile}
                                            />
                                            <Button
                                                label={isSubmitting ? "Enregistrement..." : "Enregistrer"}
                                                icon={isSubmitting ? "pi pi-spin pi-spinner" : "pi pi-save"}
                                                onClick={handleSubmit}
                                                disabled={isSubmitting || !titre || (type.name !== 'lien' && !hasSelectedFile) || (type.name === 'lien' && !linkUrl)}
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
                                                field="fichier"
                                                header={type.name === 'lien' ? 'URL' : 'Fichier'}
                                                sortable
                                                style={{ minWidth: '5rem' }}
                                                body={(rowData) => rowData.fichier || rowData.url || 'Non disponible'}
                                            />
                                            <Column
                                                field="date_ajout"
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