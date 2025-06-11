import React, { useState, useEffect, useRef } from 'react';
import { TabView, TabPanel } from 'primereact/tabview';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { InputText } from 'primereact/inputtext';
import { InputTextarea } from 'primereact/inputtextarea';
import { Calendar } from 'primereact/calendar';
import { motion, AnimatePresence } from 'framer-motion';
import { Toast } from 'primereact/toast';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';
import { FileUpload } from 'primereact/fileupload';
import { getAgendas, createAgenda, updateAgenda, uploadAgendaFile, deleteAgenda } from '../../Services/adminAgendaService';
import { getAdminMentions, getAdminNiveaux } from '../../Services/bibliothequeAdminService';

const BASE_URL = 'http://localhost:8000';

const periodeOptions = [
    { label: 'Tout', value: 'Tout' },
    { label: 'Semaine', value: 'Semaine' },
    { label: 'Mois', value: 'Mois' },
    { label: 'Trimestre', value: 'Trimestre' },
    { label: 'Semestre', value: 'Semestre' },
];

const typeOptions = [
    { label: 'Cours', value: 'COURS' },
    { label: 'Évènement', value: 'EVENEMENT' },
];

export default function AgendaAdmin() {
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [currentItemId, setCurrentItemId] = useState(null);
    const [agendas, setAgendas] = useState([]);
    const [mentions, setMentions] = useState([]);
    const [niveaux, setNiveaux] = useState([]);
    const [parcoursOptions, setParcoursOptions] = useState([]);
    const [agendaForm, setAgendaForm] = useState({
        titre: '',
        date: null,
        dateExpiration: null,
        description: '',
        lien: '',
        type: 'COURS',
        imageFile: null,
        videoFile: null,
        mention: null,
        niveau: null,
        parcours: null,
    });

    const toast = useRef(null);
    const fileUploadRef = useRef(null);

    useEffect(() => {
        const fetchAgendas = async () => {
            try {
                const data = await getAgendas();
                setAgendas(data || []); // Correctly set processed items
            } catch (error) {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message,
                    life: 3000,
                });
            }
        };
        fetchAgendas();
    }, []);

    useEffect(() => {
        const fetchMentions = async () => {
            try {
                const data = await getAdminMentions();
                setMentions(data);
            } catch (error) {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message,
                    life: 3000,
                });
            }
        };
        fetchMentions();
    }, []);

    useEffect(() => {
        if (agendaForm.mention && !isEditing) {
            const fetchNiveaux = async () => {
                try {
                    const data = await getAdminNiveaux(agendaForm.mention);
                    setNiveaux(data);
                    setParcoursOptions([]);
                    setAgendaForm(prev => ({ ...prev, niveau: null, parcours: null }));
                } catch (error) {
                    toast.current.show({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: error.message,
                        life: 3000,
                    });
                }
            };
            fetchNiveaux();
        } else if (!agendaForm.mention && !isEditing) {
            setNiveaux([]);
            setParcoursOptions([]);
            setAgendaForm(prev => ({ ...prev, niveau: null, parcours: null }));
        }
    }, [agendaForm.mention, isEditing]);

    useEffect(() => {
        if (agendaForm.niveau) {
            const selectedNiveau = niveaux.find(niveau => niveau.value === agendaForm.niveau);
            if (selectedNiveau && selectedNiveau.parcours) {
                setParcoursOptions(selectedNiveau.parcours);
                // Only reset parcours if the current parcours is invalid or not set
                if (agendaForm.parcours && !selectedNiveau.parcours.some(p => p.value === agendaForm.parcours)) {
                    setAgendaForm(prev => ({ ...prev, parcours: null }));
                }
            } else {
                setParcoursOptions([]);
                setAgendaForm(prev => ({ ...prev, parcours: null }));
            }
        } else {
            setParcoursOptions([]);
            setAgendaForm(prev => ({ ...prev, parcours: null }));
        }
    }, [agendaForm.niveau, niveaux, agendaForm.parcours]);

    const filterData = (data) => {
        if (periodeFilter === 'Tout') return data;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const endDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine': endDate.setDate(today.getDate() + 7); break;
            case 'Mois': endDate.setMonth(today.getMonth() + 1); break;
            case 'Trimestre': endDate.setMonth(today.getMonth() + 3); break;
            case 'Semestre': endDate.setMonth(today.getMonth() + 6); break;
            default: return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            itemDate.setHours(0, 0, 0, 0);
            return itemDate >= today && itemDate <= endDate;
        });
    };

    const handleFormChange = (e) => {
        const { name, value } = e.target;
        console.log('handleFormChange:', { name, value }); // Log pour déboguer
        setAgendaForm(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleDateChange = (e) => {
        setAgendaForm(prev => ({
            ...prev,
            date: e.value,
        }));
    };

    const handleDateExpirationChange = (e) => {
        setAgendaForm(prev => ({
            ...prev,
            dateExpiration: e.value,
        }));
    };

    const handleFileUpload = (e) => {
        const file = e.files[0];
        if (file) {
            const fileUrl = URL.createObjectURL(file);
            if (file.type.startsWith('image')) {
                setAgendaForm(prev => ({
                    ...prev,
                    imageFile: { file, url: fileUrl, name: file.name },
                    videoFile: null,
                }));
            } else if (file.type.startsWith('video')) {
                setAgendaForm(prev => ({
                    ...prev,
                    videoFile: { file, url: fileUrl, name: file.name },
                    imageFile: null,
                }));
            }
            if (fileUploadRef.current) fileUploadRef.current.clear();
        }
    };

    const resetForm = () => {
        setAgendaForm({
            titre: '',
            date: null,
            dateExpiration: null,
            description: '',
            lien: '',
            type: 'COURS',
            imageFile: null,
            videoFile: null,
            mention: null,
            niveau: null,
            parcours: null,
        });
        setIsEditing(false);
        setCurrentItemId(null);
        if (fileUploadRef.current) fileUploadRef.current.clear();
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            console.log('Submitting agendaForm:', agendaForm);
            const agendaData = {
                titre: agendaForm.titre,
                description: agendaForm.description,
                date: agendaForm.date,
                dateExpiration: agendaForm.dateExpiration || agendaForm.date,
                type: agendaForm.type,
                url: agendaForm.lien || null,
                mention: agendaForm.mention,
                niveau: agendaForm.niveau,
                parcours: agendaForm.parcours,
            };

            console.log('Agenda data:', agendaData);
            if (isEditing) {
                console.log('Updating agenda with ID:', currentItemId);
                await updateAgenda(currentItemId, agendaData);
                if (agendaForm.imageFile || agendaForm.videoFile) {
                    await uploadAgendaFile(
                        currentItemId,
                        agendaForm.imageFile?.file,
                        agendaForm.videoFile?.file,
                        (percent) => console.log(`Upload: ${percent}%`)
                    );
                }
                toast.current.show({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Agenda modifié avec succès',
                    life: 3000,
                });
            } else {
                console.log('Creating new agenda');
                await createAgenda(
                    agendaData,
                    agendaForm.imageFile?.file,
                    agendaForm.videoFile?.file,
                    (percent) => console.log(`Upload: ${percent}%`)
                );
                toast.current.show({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Agenda ajouté avec succès',
                    life: 3000,
                });
            }

            // Refetch agendas
            const updatedAgendas = await getAgendas();
            setAgendas(updatedAgendas || []);

            setShowCreateDialog(false);
            resetForm();
        } catch (error) {
            console.error('Error in handleSubmit:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Une erreur est survenue',
                life: 3000,
            });
        }
    };

    const handleEdit = async (type, id) => {
        console.log('handleEdit called with:', { type, id });
        if (!id || id === 'undefined' || isNaN(parseInt(id))) {
            console.error('Invalid ID:', id);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'ID invalide',
                life: 3000,
            });
            return;
        }
        const itemToEdit = agendas.find(item => item.id === parseInt(id));
        if (!itemToEdit) {
            console.error('Item not found for ID:', id);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Agenda non trouvé',
                life: 3000,
            });
            return;
        }

        console.log('Item found:', itemToEdit);
        console.log('Parcours data:', { parcoursId: itemToEdit.parcoursId, parcours: itemToEdit.parcours }); // Log pour déboguer
        let niveauxData = [];
        let parcoursData = [];
        let parcoursName = null;

        if (itemToEdit.mentionId) {
            try {
                console.log('Fetching niveaux for mentionId:', itemToEdit.mentionId);
                niveauxData = await getAdminNiveaux(itemToEdit.mentionId);
                console.log('Niveaux data:', niveauxData);
                setNiveaux(niveauxData);
                if (itemToEdit.niveauId) {
                    const selectedNiveau = niveauxData.find(niveau => niveau.value === itemToEdit.niveauId);
                    console.log('Selected niveau:', selectedNiveau);
                    if (selectedNiveau && selectedNiveau.parcours) {
                        parcoursData = selectedNiveau.parcours;
                        setParcoursOptions(parcoursData);
                        // Map parcoursId or parcours to parcours.name
                        const parcoursMatch = selectedNiveau.parcours.find(p =>
                            p.id === itemToEdit.parcoursId ||
                            p.value === itemToEdit.parcours ||
                            p.label === itemToEdit.parcours ||
                            p.value === itemToEdit.parcoursId
                        );
                        console.log('Parcours match:', parcoursMatch);
                        parcoursName = parcoursMatch ? parcoursMatch.value : null;
                    } else {
                        console.log('No parcours found for niveauId:', itemToEdit.niveauId);
                        setParcoursOptions([]);
                    }
                } else {
                    console.log('No niveauId provided');
                    setParcoursOptions([]);
                }
            } catch (error) {
                console.error('Error fetching niveaux:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message || 'Erreur lors de la récupération des niveaux',
                    life: 3000,
                });
                return;
            }
        } else {
            console.log('No mentionId provided');
            setNiveaux([]);
            setParcoursOptions([]);
        }

        setAgendaForm({
            titre: itemToEdit.titre,
            date: new Date(itemToEdit.date),
            dateExpiration: new Date(itemToEdit.dateExpiration),
            description: itemToEdit.description,
            lien: itemToEdit.url || '',
            type: itemToEdit.type,
            imageFile: null,
            videoFile: null,
            mention: itemToEdit.mentionId || null,
            niveau: itemToEdit.niveauId || null,
            parcours: parcoursName,
        });

        console.log('Setting agendaForm:', {
            titre: itemToEdit.titre,
            date: new Date(itemToEdit.date),
            dateExpiration: new Date(itemToEdit.dateExpiration),
            description: itemToEdit.description,
            lien: itemToEdit.url || '',
            type: itemToEdit.type,
            mention: itemToEdit.mentionId || null,
            niveau: itemToEdit.niveauId || null,
            parcours: parcoursName,
        });

        setIsEditing(true);
        setCurrentItemId(id);
        setShowCreateDialog(true);
    };

    const confirmDelete = (type, id, event) => {
        event.stopPropagation();
        confirmDialog({
            message: 'Êtes-vous sûr de vouloir supprimer cet élément ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: () => deleteItem(type, id),
            acceptClassName: 'p-button-danger',
            rejectClassName: 'p-button-secondary',
        });
    };

    const deleteItem = async (type, id) => {
        if (!id || id === 'undefined' || isNaN(parseInt(id))) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'ID invalide',
                life: 3000,
            });
            return;
        }
        try {
            await deleteAgenda(id);
            setAgendas(prev => prev.filter(item => item.id !== parseInt(id)));
            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Agenda supprimé avec succès',
                life: 3000,
            });
        } catch (error) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message,
                life: 3000,
            });
        }
    };

    const renderItem = (item, type) => (
        <div key={item.id} className="flex flex-col md:flex-row border-[1px] mb-4 w-full md:w-4/5 rounded-lg bg-white shadow-md">
            <div className="flex flex-col p-4 text-center md:w-28 flex-shrink-0">
                <h1 className="text-5xl font-bold text-gray-800">{new Date(item.date).getDate()}</h1>
                <h2 className="text-lg font-semibold text-gray-700 uppercase">
                    {new Date(item.date).toLocaleString('default', { month: 'short' })}
                </h2>
            </div>
            <Divider layout="vertical" className="hidden md:block h-auto" />
            <div className="flex flex-col p-4 flex-1 min-h-[180px]">
                <div className="flex justify-between items-start mb-2">
                    <h1 className="text-xl font-semibold text-gray-800 truncate flex-1">{item.titre}</h1>
                    <div className="flex gap-2 ml-3">
                        {item.type !== 'EXAMEN' && (
                            <button className="text-blue-500 hover:text-blue-700" onClick={() => handleEdit(item.type.toLowerCase(), item.id)}>
                                <i className="pi pi-pencil"></i>
                            </button>
                        )}
                        {item.type !== 'EXAMEN' && (
                            <button className="text-red-500 hover:text-red-700" onClick={(e) => confirmDelete(item.type.toLowerCase(), item.id, e)}>
                                <i className="pi pi-trash"></i>
                            </button>
                        )}
                    </div>
                </div>
                {(item.mentionName || item.niveauNom || item.parcours) && (
                    <div className="mb-2">
                        <span className="text-sm font-medium text-gray-600">
                            {item.mentionName} {item.niveauNom && `- ${item.niveauNom}`} {item.parcours && `- ${item.parcours}`}
                        </span>
                    </div>
                )}
                <div className="mb-3">
                    <p className="text-gray-600 whitespace-pre-wrap break-words">{item.description}</p>
                </div>
                {item.url && (
                    <div className="mb-3">
                        <a href={item.url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center text-blue-600 hover:underline break-all">
                            <i className="pi pi-link mr-2"></i>Lien vers la ressource
                        </a>
                    </div>
                )}
                {(item.image || item.video) && (
                    <div className="mt-auto">
                        {item.image && (
                            <div className="border border-gray-200 rounded-lg overflow-hidden flex items-center justify-center">
                                <img src={`${BASE_URL}${item.image}`} alt="Image agenda" className="w-[500px]" onError={(e) => e.target.src = '/fallback-image.jpg'} />
                            </div>
                        )}
                        {item.video && (
                            <div className="mt-3">
                                <div className="relative pt-[56.25%] bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                    <video controls className="absolute inset-0 w-full h-full">
                                        <source src={`${BASE_URL}${item.video}`} type="video/mp4" />
                                    </video>
                                </div>
                            </div>
                        )}
                    </div>
                )}
                <div className="mt-3 pt-2 border-t border-gray-100">
                    <p className="text-sm font-medium text-gray-700">{item.nomAuteur || 'Non spécifié'}</p>
                </div>
            </div>
        </div>
    );

    const renderFilterSection = () => (
        <div className="mb-3 flex gap-2 w-full md:w-auto">
            <Dropdown
                value={periodeFilter}
                onChange={(e) => setPeriodeFilter(e.value)}
                options={periodeOptions}
                optionLabel="label"
                placeholder="Période"
                panelClassName="font-poppins text-sm"
                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
            />
            <Button
                label="Créer un agenda"
                icon="pi pi-calendar-plus"
                className="p-button-success w-80"
                onClick={() => {
                    resetForm();
                    setShowCreateDialog(true);
                }}
            />
        </div>
    );

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="top-right" />
            <ConfirmDialog />
            <div className="card custom-scrollbar h-[90vh] overflow-y-auto">
                <TabView className="custom-tabview">
                    <TabPanel header="Cours" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendas.filter(item => item.type === 'COURS')).length > 0 ? (
                                filterData(agendas.filter(item => item.type === 'COURS')).map(item => renderItem(item, 'cours'))
                            ) : (
                                <p className="text-gray-500">Aucun cours prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Examens" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendas.filter(item => item.type === 'EXAMEN')).length > 0 ? (
                                filterData(agendas.filter(item => item.type === 'EXAMEN')).map(item => renderItem(item, 'examen'))
                            ) : (
                                <p className="text-gray-500">Aucun examen prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Evènements" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendas.filter(item => item.type === 'EVENEMENT')).length > 0 ? (
                                filterData(agendas.filter(item => item.type === 'EVENEMENT')).map(item => renderItem(item, 'evenement'))
                            ) : (
                                <p className="text-gray-500">Aucun évènement prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                </TabView>

                <AnimatePresence>
                    {showCreateDialog && (
                        <motion.div
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                            onClick={() => {
                                setShowCreateDialog(false);
                                resetForm();
                            }}
                        >
                            <motion.div
                                initial={{ y: 20 }}
                                animate={{ y: 0 }}
                                exit={{ y: 20 }}
                                className="bg-white rounded-lg shadow-xl w-full max-w-3xl"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="bg-blue-500 text-white px-6 py-4 rounded-t-lg flex justify-between items-center sticky top-0 z-10">
                                    <h1 className="text-xl font-semibold">
                                        {isEditing ? 'Modifier un agenda' : 'Créer un nouvel agenda'}
                                    </h1>
                                    <button
                                        className="text-white hover:bg-blue-600 rounded-full px-2 py-1 items-center justify-center"
                                        onClick={() => {
                                            setShowCreateDialog(false);
                                            resetForm();
                                        }}
                                    >
                                        <i className="pi pi-times"></i>
                                    </button>
                                </div>

                                <form onSubmit={handleSubmit} className="p-6 space-y-4 overflow-y-auto h-[85vh] custom-scrollbar">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Type*</label>
                                            <Dropdown
                                                name="type"
                                                value={agendaForm.type}
                                                onChange={handleFormChange}
                                                options={typeOptions}
                                                optionLabel="label"
                                                className="w-full"
                                                disabled={isEditing}
                                            />
                                        </div>

                                        {agendaForm.type === 'COURS' && (
                                            <div className="w-60">
                                                <label className="block text-sm font-medium text-gray-700 mb-1">Mention*</label>
                                                <Dropdown
                                                    name="mention"
                                                    value={agendaForm.mention}
                                                    onChange={handleFormChange}
                                                    options={mentions}
                                                    optionLabel="label"
                                                    className="w-full"
                                                    placeholder="Sélectionnez une mention"
                                                    required={agendaForm.type === 'COURS'}
                                                />
                                            </div>
                                        )}

                                        {agendaForm.type === 'COURS' && agendaForm.mention && (
                                            <div className="w-60">
                                                <label className="block text-sm font-medium text-gray-700 mb-1">Niveau*</label>
                                                <Dropdown
                                                    name="niveau"
                                                    value={agendaForm.niveau}
                                                    onChange={handleFormChange}
                                                    options={niveaux}
                                                    optionLabel="label"
                                                    className="w-full"
                                                    placeholder="Sélectionnez un niveau"
                                                    required={agendaForm.type === 'COURS'}
                                                />
                                            </div>
                                        )}

                                        {agendaForm.type === 'COURS' && agendaForm.niveau && parcoursOptions.length > 0 && (
                                            <div className="w-60">
                                                <label className="block text-sm font-medium text-gray-700 mb-1">Parcours*</label>
                                                <Dropdown
                                                    name="parcours"
                                                    value={agendaForm.parcours}
                                                    onChange={handleFormChange}
                                                    options={parcoursOptions}
                                                    optionLabel="label"
                                                    className="w-full"
                                                    placeholder="Sélectionnez un parcours"
                                                    required={agendaForm.type === 'COURS'}
                                                />
                                            </div>
                                        )}

                                        <div className="w-80">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Titre*</label>
                                            <InputText
                                                name="titre"
                                                value={agendaForm.titre}
                                                onChange={handleFormChange}
                                                className="w-full"
                                                placeholder="Entrez le titre"
                                                required
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Date*</label>
                                            <Calendar
                                                value={agendaForm.date}
                                                onChange={handleDateChange}
                                                className="w-full"
                                                dateFormat="dd/mm/yy"
                                                showIcon
                                                required
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Date d'expiration*</label>
                                            <Calendar
                                                value={agendaForm.dateExpiration}
                                                onChange={handleDateExpirationChange}
                                                className="w-full"
                                                dateFormat="dd/mm/yy"
                                                showIcon
                                                required
                                            />
                                        </div>

                                        <div className="w-full md:col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                                            <InputTextarea
                                                name="description"
                                                value={agendaForm.description}
                                                onChange={handleFormChange}
                                                className="w-full"
                                                rows={6}
                                                placeholder="Entrez la description"
                                                required
                                            />
                                        </div>

                                        <div className="w-80">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Lien (URL)</label>
                                            <InputText
                                                name="lien"
                                                value={agendaForm.lien}
                                                onChange={handleFormChange}
                                                className="w-full"
                                                placeholder="https://example.com"
                                            />
                                        </div>

                                        <div className="w-full md:col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Fichier (Image/Video)</label>
                                            <FileUpload
                                                ref={fileUploadRef}
                                                name="file"
                                                mode="basic"
                                                accept="image/*,video/mp4,video/webm,video/ogg"
                                                maxFileSize={50000000}
                                                chooseLabel="Choisir un fichier"
                                                onSelect={handleFileUpload}
                                                auto
                                                className="w-full"
                                            />
                                            {(agendaForm.imageFile || agendaForm.videoFile) && (
                                                <div className="mt-2 text-sm text-gray-500">
                                                    Fichier sélectionné : {agendaForm.imageFile?.name || agendaForm.videoFile?.name}
                                                    {agendaForm.imageFile?.url && (
                                                        <div className="mt-2">
                                                            <img src={agendaForm.imageFile.url} alt="Preview" className="max-h-40 border rounded" />
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex justify-end pt-4 gap-2 bg-white pb-4">
                                        <Button
                                            type="button"
                                            label="Annuler"
                                            severity="secondary"
                                            onClick={() => {
                                                setShowCreateDialog(false);
                                                resetForm();
                                            }}
                                        />
                                        <Button
                                            type="submit"
                                            label={isEditing ? 'Modifier' : 'Enregistrer'}
                                            severity="success"
                                        />
                                    </div>
                                </form>
                            </motion.div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </LayoutAdmin>
    );
}