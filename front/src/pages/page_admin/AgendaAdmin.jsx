import React, { useState, useRef } from 'react';
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
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

const DEMO_IMAGES = {
    maths: 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    physique: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    conference: 'https://images.unsplash.com/photo-1579353977828-2a4eab540b9a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    chimie: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
    informatique: 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80'
};

const DEMO_VIDEOS = {
    cours: 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
    conference: 'https://samplelib.com/lib/preview/mp4/sample-10s.mp4',
    examen: 'https://samplelib.com/lib/preview/mp4/sample-15s.mp4'
};

const mentionsOptions = [
    { label: 'Communication', value: 'communication' },
    { label: 'Informatique', value: 'informatique' },
    { label: 'Économie', value: 'economie' }
];

const niveauxOptions = [
    { label: 'L1', value: 'L1' },
    { label: 'L2', value: 'L2' },
    { label: 'L3', value: 'L3' },
    { label: 'M1', value: 'M1' },
    { label: 'M2', value: 'M2' }
];

const typeOptions = [
    { label: 'Cours', value: 'cours' },
    { label: 'Examen', value: 'examen' },
    { label: 'Évènement', value: 'evenement' }
];

const periodeOptions = [
    { label: 'Tout', value: 'Tout' },
    { label: 'Semaine', value: 'Semaine' },
    { label: 'Mois', value: 'Mois' },
    { label: 'Trimestre', value: 'Trimestre' },
    { label: 'Semestre', value: 'Semestre' },
];

export default function AgendaAdmin() {
    const generateTestDate = (daysFromNow) => {
        const date = new Date();
        date.setDate(date.getDate() + daysFromNow);
        return date.toISOString().split('T')[0];
    };

    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [currentItemId, setCurrentItemId] = useState(null);
    const [agendaForm, setAgendaForm] = useState({
        titre: '',
        date: null,
        description: '',
        lien: '',
        type: 'cours',
        media: null,
        mention: null,
        niveau: null
    });

    const [coursData, setCoursData] = useState([
        {
            id: 1,
            titre: 'Cours de Mathématiques',
            date: generateTestDate(1),
            description: 'Introduction aux équations différentielles',
            professeur: 'Prof. Dupont',
            media: {
                type: 'image',
                url: DEMO_IMAGES.maths,
                name: 'maths.jpg'
            }
        },
        {
            id: 2,
            titre: 'Cours de Physique',
            date: generateTestDate(3),
            description: 'Mécanique quantique avancée',
            professeur: 'Prof. Martin',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.cours,
                thumbnail: DEMO_IMAGES.physique,
                name: 'physique.mp4'
            }
        },
        {
            id: 3,
            titre: 'Cours de Chimie',
            date: generateTestDate(10),
            description: 'Chimie organique - Les alcènes',
            professeur: 'Prof. Legrand',
            media: {
                type: 'image',
                url: DEMO_IMAGES.chimie,
                name: 'chimie.jpg'
            }
        },
        {
            id: 4,
            titre: 'Cours d\'Informatique',
            date: generateTestDate(30),
            description: 'Algorithmes avancés',
            professeur: 'Prof. Dubois',
            media: {
                type: 'image',
                url: DEMO_IMAGES.informatique,
                name: 'informatique.jpg'
            }
        },
        {
            id: 5,
            titre: 'Cours de Biologie',
            date: generateTestDate(90),
            description: 'Génétique moléculaire',
            professeur: 'Prof. Bernard',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.cours,
                thumbnail: DEMO_IMAGES.physique,
                name: 'biologie.mp4'
            }
        }
    ]);

    const [examensData, setExamensData] = useState([
        {
            id: 1,
            titre: 'Examen de Mathématiques',
            date: generateTestDate(5),
            description: 'Examen final - Partie 1',
            professeur: 'Prof. Dupont',
            media: {
                type: 'image',
                url: DEMO_IMAGES.maths,
                name: 'examen_maths.jpg'
            }
        },
        {
            id: 2,
            titre: 'Examen de Physique',
            date: generateTestDate(8),
            description: 'Examen pratique',
            professeur: 'Prof. Martin',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.examen,
                thumbnail: DEMO_IMAGES.physique,
                name: 'examen_physique.mp4'
            }
        },
        {
            id: 3,
            titre: 'Examen de Chimie',
            date: generateTestDate(60),
            description: 'Examen théorique',
            professeur: 'Prof. Legrand',
            media: {
                type: 'image',
                url: DEMO_IMAGES.chimie,
                name: 'examen_chimie.jpg'
            }
        }
    ]);

    const [evenementsData, setEvenementsData] = useState([
        {
            id: 1,
            titre: 'Conférence sur l\'IA',
            date: generateTestDate(2),
            description: 'Conférence avec un expert en IA',
            organisateur: 'Dr. Smith',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.conference,
                thumbnail: DEMO_IMAGES.conference,
                name: 'conference_ia.mp4'
            }
        },
        {
            id: 2,
            titre: 'Journée portes ouvertes',
            date: generateTestDate(15),
            description: 'Découverte des laboratoires de recherche',
            organisateur: 'Dr. Johnson',
            media: {
                type: 'image',
                url: DEMO_IMAGES.conference,
                name: 'portes_ouvertes.jpg'
            }
        },
        {
            id: 3,
            titre: 'Séminaire de recherche',
            date: generateTestDate(45),
            description: 'Avancées récentes en physique quantique',
            organisateur: 'Prof. Einstein',
            media: {
                type: 'video',
                url: DEMO_VIDEOS.conference,
                thumbnail: DEMO_IMAGES.physique,
                name: 'seminaire_physique.mp4'
            }
        },
        {
            id: 4,
            titre: 'Remise des diplômes',
            date: generateTestDate(120),
            description: 'Cérémonie annuelle de remise des diplômes',
            organisateur: 'Directeur Université',
            media: {
                type: 'image',
                url: DEMO_IMAGES.conference,
                name: 'remise_diplomes.jpg'
            }
        }
    ]);


    const toast = useRef(null);
    const fileUploadRef = useRef(null);

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
        if (e && e.target) {
            const { name, value } = e.target;
            setAgendaForm(prev => ({
                ...prev,
                [name]: value,
                ...(name === 'mention' && { niveau: null })
            }));
        }
    };


    const handleDateChange = (e) => {
        setAgendaForm(prev => ({ ...prev, date: e.value }));
    };

    const handleFileUpload = (e) => {
        const file = e.files[0];
        if (file) {
            const fileUrl = URL.createObjectURL(file);
            setAgendaForm(prev => ({
                ...prev,
                media: {
                    file,
                    url: fileUrl,
                    name: file.name,
                    type: file.type.startsWith('image') ? 'image' : 'video',
                    ...(file.type.startsWith('video') && { thumbnail: DEMO_IMAGES.conference })
                }
            }));
            if (fileUploadRef.current) fileUploadRef.current.clear();
        }
    };

    const resetForm = () => {
        setAgendaForm({
            titre: '',
            date: null,
            description: '',
            lien: '',
            type: 'cours',
            media: null,
            mention: null,
            niveau: null
        });
        setIsEditing(false);
        setCurrentItemId(null);
        if (fileUploadRef.current) fileUploadRef.current.clear();
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const newItem = {
            id: isEditing ? currentItemId : Math.max(...[...coursData, ...examensData, ...evenementsData].map(i => i.id), 0) + 1,
            titre: agendaForm.titre,
            date: agendaForm.date.toISOString().split('T')[0],
            description: agendaForm.description,
            ...(agendaForm.lien && { lien: agendaForm.lien }),
            media: agendaForm.media?.file ? {
                type: agendaForm.media.type,
                name: agendaForm.media.name,
                url: agendaForm.media.url,
                ...(agendaForm.media.type === 'video' && { thumbnail: agendaForm.media.thumbnail })
            } : isEditing ? agendaForm.media : null,
            ...(agendaForm.type === 'cours' && {
                professeur: 'Professeur',
                mention: agendaForm.mention,
                niveau: agendaForm.niveau
            }),
            ...(agendaForm.type === 'examen' && {
                professeur: 'Professeur',
                mention: agendaForm.mention,
                niveau: agendaForm.niveau
            }),
            ...(agendaForm.type === 'evenement' && { organisateur: 'Organisateur' })
        };

        if (isEditing) {
            switch (agendaForm.type) {
                case 'cours': setCoursData(coursData.map(item => item.id === currentItemId ? newItem : item)); break;
                case 'examen': setExamensData(examensData.map(item => item.id === currentItemId ? newItem : item)); break;
                case 'evenement': setEvenementsData(evenementsData.map(item => item.id === currentItemId ? newItem : item)); break;
            }
            toast.current.show({ severity: 'success', summary: 'Succès', detail: 'Élément modifié avec succès', life: 3000 });
        } else {
            switch (agendaForm.type) {
                case 'cours': setCoursData([...coursData, newItem]); break;
                case 'examen': setExamensData([...examensData, newItem]); break;
                case 'evenement': setEvenementsData([...evenementsData, newItem]); break;
            }
            toast.current.show({ severity: 'success', summary: 'Succès', detail: 'Élément ajouté à l\'agenda', life: 3000 });
        }

        setShowCreateDialog(false);
        resetForm();
    };

    const handleEdit = (type, id) => {
        let itemToEdit;
        switch (type) {
            case 'cours': itemToEdit = coursData.find(item => item.id === id); break;
            case 'examen': itemToEdit = examensData.find(item => item.id === id); break;
            case 'evenement': itemToEdit = evenementsData.find(item => item.id === id); break;
        }

        if (itemToEdit) {
            setAgendaForm({
                titre: itemToEdit.titre,
                date: new Date(itemToEdit.date),
                description: itemToEdit.description,
                lien: itemToEdit.lien || '',
                type: type,
                media: itemToEdit.media || null,
                mention: itemToEdit.mention || null,
                niveau: itemToEdit.niveau || null
            });
            setIsEditing(true);
            setCurrentItemId(id);
            setShowCreateDialog(true);
        }
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
            rejectClassName: 'p-button-secondary'
        });
    };

    const deleteItem = (type, id) => {
        switch (type) {
            case 'cours': setCoursData(coursData.filter(item => item.id !== id)); break;
            case 'examen': setExamensData(examensData.filter(item => item.id !== id)); break;
            case 'evenement': setEvenementsData(evenementsData.filter(item => item.id !== id)); break;
        }
        toast.current.show({ severity: 'success', summary: 'Succès', detail: 'Élément supprimé de l\'agenda', life: 3000 });
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
                        <button className="text-blue-500 hover:text-blue-700" onClick={() => handleEdit(type, item.id)}>
                            <i className="pi pi-pencil"></i>
                        </button>
                        <button className="text-red-500 hover:text-red-700" onClick={(e) => confirmDelete(type, item.id, e)}>
                            <i className="pi pi-trash"></i>
                        </button>
                    </div>
                </div>

                {(item.mention || item.niveau) && (
                    <div className="mb-2">
                        <span className="text-sm font-medium text-gray-600">
                            {item.mention} {item.niveau && `- ${item.niveau}`}
                        </span>
                    </div>
                )}

                <div className="mb-3">
                    <p className="text-gray-600 whitespace-pre-wrap break-words">{item.description}</p>
                </div>

                {item.lien && (
                    <div className="mb-3">
                        <a href={item.lien} target="_blank" rel="noopener noreferrer" className="inline-flex items-center text-blue-600 hover:underline break-all">
                            <i className="pi pi-link mr-2"></i>Lien vers la ressource
                        </a>
                    </div>
                )}

                {item.media && (
                    <div className="mt-auto">
                        {item.media.type === 'image' ? (
                            <div className="border border-gray-200 rounded-lg overflow-hidden">
                                <img src={item.media.url} alt={item.media.name || 'Image'} className="w-full h-auto" />
                                {item.media.name && <div className="p-2 bg-gray-50 text-sm text-gray-600 truncate">{item.media.name}</div>}
                            </div>
                        ) : item.media.type === 'video' ? (
                            <div className="mt-3">
                                <div className="relative pt-[56.25%] bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                    <video controls className="absolute inset-0 w-full h-full" poster={item.media.thumbnail}>
                                        <source src={item.media.url} type="video/mp4" />
                                    </video>
                                </div>
                                {item.media.name && <div className="mt-1 text-sm text-gray-600 truncate">{item.media.name}</div>}
                            </div>
                        ) : (
                            <div className="p-3 bg-gray-50 rounded-lg border border-gray-200 flex items-center">
                                <i className="pi pi-file text-gray-500 text-xl mr-3"></i>
                                <span className="text-gray-700">{item.media.name || 'Fichier joint'}</span>
                            </div>
                        )}
                    </div>
                )}

                <div className="mt-3 pt-2 border-t border-gray-100">
                    <p className="text-sm font-medium text-gray-700">
                        {item.professeur || item.organisateur || 'Non spécifié'}
                    </p>
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
                <TabView className='custom-tabview'>
                    <TabPanel header="Cours" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(coursData).length > 0 ? (
                                filterData(coursData).map(item => renderItem(item, 'cours'))
                            ) : (
                                <p className="text-gray-500">Aucun cours prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Examens" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(examensData).length > 0 ? (
                                filterData(examensData).map(item => renderItem(item, 'examen'))
                            ) : (
                                <p className="text-gray-500">Aucun examen prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                    <TabPanel header="Evènements" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(evenementsData).length > 0 ? (
                                filterData(evenementsData).map(item => renderItem(item, 'evenement'))
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
                                        <div className='w-60'>
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

                                        {(agendaForm.type === 'cours' || agendaForm.type === 'examen') && (
                                            <div className='w-60'>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">Mention*</label>
                                                <Dropdown
                                                    name="mention"
                                                    value={agendaForm.mention}
                                                    onChange={handleFormChange}
                                                    options={mentionsOptions}
                                                    optionLabel="label"
                                                    className="w-full"
                                                    placeholder="Sélectionnez une mention"
                                                    required={agendaForm.type === 'cours' || agendaForm.type === 'examen'}
                                                />
                                            </div>
                                        )}

                                        {(agendaForm.type === 'cours' || agendaForm.type === 'examen') && agendaForm.mention && (
                                            <div className='w-60'>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">Niveau*</label>
                                                <Dropdown
                                                    name="niveau"
                                                    value={agendaForm.niveau}
                                                    onChange={handleFormChange}
                                                    options={niveauxOptions}
                                                    optionLabel="label"
                                                    className="w-full"
                                                    placeholder="Sélectionnez un niveau"
                                                    required={agendaForm.type === 'cours' || agendaForm.type === 'examen'}
                                                />
                                            </div>
                                        )}

                                        <div className='w-80'>
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

                                        <div className='w-60'>
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

                                        <div className="w-full md:col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                                            <ReactQuill
                                                value={agendaForm.description}
                                                onChange={handleFormChange}
                                                modules={{
                                                    toolbar: [
                                                        ['bold', 'italic', 'underline', 'strike'],
                                                        ['blockquote'],
                                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                                                        [{ 'header': [1, 2, 3, false] }],
                                                        [{ 'color': [] }, { 'background': [] }],
                                                        [{ 'align': [] }],
                                                        ['clean']
                                                    ]
                                                }}
                                                style={{ height: '250px', marginBottom: '50px' }}
                                            />
                                        </div>

                                        <div className='w-80'>
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
                                                name="media"
                                                mode="basic"
                                                accept="image/*,video/*"
                                                maxFileSize={10000000}
                                                chooseLabel="Choisir un fichier"
                                                onSelect={handleFileUpload}
                                                auto
                                                className="w-full"
                                            />
                                            {agendaForm.media && (
                                                <div className="mt-2 text-sm text-gray-500">
                                                    Fichier sélectionné: {agendaForm.media.name}
                                                    {agendaForm.media.url && agendaForm.media.type === 'image' && (
                                                        <div className="mt-2">
                                                            <img src={agendaForm.media.url} alt="Preview" className="max-h-40 border rounded" />
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