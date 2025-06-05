import React, { useState, useEffect, useRef } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { TabView, TabPanel } from 'primereact/tabview';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Button } from 'primereact/button';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { InputText } from 'primereact/inputtext';
import { Dialog } from 'primereact/dialog';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';
import { Toast } from 'primereact/toast';
import { Dropdown } from 'primereact/dropdown';
import { MultiSelect } from 'primereact/multiselect'; // Added for multi-select
import { FileUpload } from 'primereact/fileupload';
import { InputSwitch } from 'primereact/inputswitch';
import { motion, AnimatePresence } from 'framer-motion';
import parametreService from '../../Services/parametreService';

const Setting = () => {
    const toast = useRef(null);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [visibleDialog, setVisibleDialog] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [activeTab, setActiveTab] = useState(0);
    const [formData, setFormData] = useState({});

    // Données dynamiques récupérées depuis l'API
    const [mentions, setMentions] = useState([]);
    const [niveaux, setNiveaux] = useState([]);
    const [parcours, setParcours] = useState([]);
    const [semestres, setSemestres] = useState([]);
    const [ues, setUes] = useState([]);
    const [ecs, setEcs] = useState([]);
    const [annees, setAnnees] = useState([]);
    const [profs, setProfs] = useState([]);

    // URL de base pour les images
    const IMAGE_BASE_URL = 'http://localhost:8000/uploads/icons/';

    const extractIdFromIri = (iri) => {
        if (!iri) return null;
        if (typeof iri === 'object' && iri['@id']) {
            const parts = iri['@id'].split('/');
            return parseInt(parts[parts.length - 1], 10);
        }
        if (typeof iri === 'string') {
            const parts = iri.split('/');
            return parseInt(parts[parts.length - 1], 10);
        }
        return parseInt(iri, 10);
    };

    // Options pour les Dropdown et MultiSelect
    const mentionOptions = mentions.map(m => ({ label: m.name, value: m.id }));
    const niveauOptions = niveaux.map(n => ({ label: n.nom, value: n.id }));
    const parcoursOptions = parcours.map(p => ({ label: p.name, value: p.id }));
    const semestreOptions = semestres.map(s => ({ label: s.name || s.nom, value: s.id }));
    const ueOptions = ues.map(u => ({ label: `UE${u.name || u.numero} - ${getMentionName(u.mention)}`, value: u.id }));
    const profOptions = profs.map(p => ({
        label: p.name || 'Nom inconnu',
        value: p.id
    }));
    const cycleOptions = [
        { label: 'Licence', value: 'Licence' },
        { label: 'Master', value: 'Master' },
        { label: 'Doctorat', value: 'Doctorat' }
    ];

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [
                    mentionsData,
                    niveauxData,
                    parcoursData,
                    semestresData,
                    uesData,
                    ecsData,
                    anneesData,
                    profsData
                ] = await Promise.all([
                    parametreService.getMentions(),
                    parametreService.getNiveaux(),
                    parametreService.getParcours(),
                    parametreService.getSemestres(),
                    parametreService.getUes(),
                    parametreService.getEcs(),
                    parametreService.getYears(),
                    parametreService.getProfs()
                ]);

                setMentions(mentionsData.map(item => ({
                    id: item.id,
                    name: item.name,
                    icon: item.icon
                })));
                setNiveaux(niveauxData.map(item => ({
                    id: item.id,
                    nom: item.nom,
                    code: item.code,
                    cycle: item.cycle,
                    ordre: item.ordre
                })));
                setParcours(parcoursData.map(item => ({
                    id: item.id,
                    name: item.name,
                    full_name: item.full_name,
                    mention: extractIdFromIri(item.mention),
                    niveau: extractIdFromIri(item.niveau)
                })));
                setSemestres(semestresData.map(item => ({
                    id: item.id,
                    name: item.name || item.nom,
                    code: item.code,
                    niveau: extractIdFromIri(item.niveau)
                })));
                setUes(uesData.map(item => ({
                    id: item.id,
                    name: item.name,
                    code: item.code,
                    mention: extractIdFromIri(item.mention),
                    semestre: extractIdFromIri(item.semestre),
                    parcours: item.ueParcours ? item.ueParcours.map(up => extractIdFromIri(up.parcours)) : []
                })));
                setEcs(ecsData.map(item => ({
                    id: item.id,
                    name: item.name,
                    code: item.code,
                    ue: extractIdFromIri(item.ue),
                    prof: extractIdFromIri(item.prof),
                    coeff: item.coeff,
                    status: item.status,
                    profName: item.profName
                })));
                setAnnees(anneesData.map(item => ({
                    id: item.id,
                    year: item.year,
                    current: item.current
                })));
                setProfs(profsData.map(item => ({
                    id: item.id, // Utilisez directement item.id
                    name: item.userName || 'Nom inconnu' // Utilisez item.userName qui contient déjà le nom complet
                })));
            } catch (error) {
                showToast('error', error.message || 'Erreur lors du chargement des données');
                console.error(error);
            }
        };

        fetchData();
    }, []);

    function getMentionName(id) {
        const mention = mentions.find(m => m.id === id);
        return mention ? mention.name : '';
    }

    function getProfName(id) {
        const prof = profs.find(p => p.id === id);
        return prof ? prof.name : '-';
    }

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const showToast = (severity, message) => {
        toast.current.show({ severity, summary: severity === 'success' ? 'Succès' : 'Erreur', detail: message, life: 3000 });
    };

    const handleAdd = () => {
        setSelectedItem(null);
        setFormData({});
        setVisibleDialog(true);
    };

    const handleEdit = (rowData) => {
        setSelectedItem(rowData);
        setFormData({ ...rowData, prof: rowData.prof });
        setVisibleDialog(true);
    };

    const handleDelete = (rowData) => {
        confirmDialog({
            message: 'Êtes-vous sûr de vouloir supprimer cet élément ?',
            header: 'Confirmation',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: () => deleteItem(rowData),
            reject: () => { }
        });
    };

    const deleteItem = async (item) => {
        try {
            switch (activeTab) {
                case 0:
                    await parametreService.deleteMention(item.id);
                    setMentions(mentions.filter(i => i.id !== item.id));
                    break;
                case 1:
                    await parametreService.deleteNiveau(item.id);
                    setNiveaux(niveaux.filter(i => i.id !== item.id));
                    break;
                case 2:
                    await parametreService.deleteParcours(item.id);
                    setParcours(parcours.filter(i => i.id !== item.id));
                    break;
                case 3:
                    await parametreService.deleteSemestre(item.id);
                    setSemestres(semestres.filter(i => i.id !== item.id));
                    break;
                case 4:
                    await parametreService.deleteUe(item.id);
                    setUes(ues.filter(i => i.id !== item.id));
                    break;
                case 5:
                    await parametreService.deleteEc(item.id);
                    setEcs(ecs.filter(i => i.id !== item.id));
                    break;
                case 6:
                    await parametreService.deleteYear(item.id);
                    setAnnees(annees.filter(i => i.id !== item.id));
                    break;
                default:
                    break;
            }
            showToast('success', 'Élément supprimé avec succès');
        } catch (error) {
            showToast('error', error.message || 'Erreur lors de la suppression');
            console.error(error);
        }
    };

    const saveItem = async () => {
        try {
            let dataToSend;
            switch (activeTab) {
                case 0: // Mention
                    dataToSend = { name: formData.name, iconFile: formData.iconFile };
                    if (selectedItem) {
                        await parametreService.updateMention(selectedItem.id, { name: formData.name });
                        if (formData.iconFile) {
                            await parametreService.uploadMentionIcon(selectedItem.id, formData.iconFile);
                        }
                        setMentions(mentions.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, name: formData.name, icon: formData.iconFile ? `${Date.now()}` : item.icon }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createMention(dataToSend);
                        setMentions([...mentions, { ...response, id: response.id, name: response.name, icon: response.icon }]);
                    }
                    break;
                case 1: // Niveau
                    dataToSend = {
                        nom: formData.nom,
                        code: formData.code,
                        cycle: formData.cycle,
                        ordre: parseInt(formData.ordre)
                    };
                    if (selectedItem) {
                        await parametreService.updateNiveau(selectedItem.id, dataToSend);
                        setNiveaux(niveaux.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, nom: formData.nom, code: formData.code, cycle: formData.cycle, ordre: parseInt(formData.ordre) }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createNiveau(dataToSend);
                        setNiveaux([...niveaux, { ...response, id: response.id, nom: response.nom, code: response.code, cycle: response.cycle, ordre: response.ordre }]);
                    }
                    break;
                case 2: // Parcours
                    dataToSend = {
                        name: formData.name,
                        full_name: formData.full_name,
                        mention: formData.mention,
                        niveau: formData.niveau
                    };
                    if (selectedItem) {
                        await parametreService.updateParcours(selectedItem.id, dataToSend);
                        setParcours(parcours.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, name: formData.name, full_name: formData.full_name, mention: formData.mention, niveau: formData.niveau }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createParcours(dataToSend);
                        setParcours([...parcours, { ...response, id: response.id, name: response.name, full_name: response.full_name, mention: extractIdFromIri(response.mention), niveau: extractIdFromIri(response.niveau) }]);
                    }
                    break;
                case 3: // Semestre
                    dataToSend = { name: formData.name, code: formData.code, niveau: formData.niveau };
                    if (selectedItem) {
                        await parametreService.updateSemestre(selectedItem.id, dataToSend);
                        setSemestres(semestres.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, name: formData.name, code: formData.code, niveau: formData.niveau }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createSemestre(dataToSend);
                        setSemestres([...semestres, { ...response, id: response.id, name: response.name, code: response.code, niveau: extractIdFromIri(response.niveau) }]);
                    }
                    break;
                case 4: // Unité d'Enseignement
                    dataToSend = {
                        name: formData.name,
                        code: formData.code,
                        mention: formData.mention,
                        semestre: formData.semestre,
                        parcours: Array.isArray(formData.parcours) ? formData.parcours : [formData.parcours].filter(Boolean)
                    };
                    if (selectedItem) {
                        await parametreService.updateUe(selectedItem.id, dataToSend);
                        setUes(ues.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, name: formData.name, code: formData.code, mention: formData.mention, semestre: formData.semestre, parcours: dataToSend.parcours }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createUe(dataToSend);
                        setUes([...ues, { ...response, id: response.id, name: response.name, code: response.code, mention: extractIdFromIri(response.mention), semestre: extractIdFromIri(response.semestre), parcours: response.ueParcours ? response.ueParcours.map(up => extractIdFromIri(up.parcours)) : [] }]);
                    }
                    break;
                case 5: // Élément Constitutif
                    dataToSend = {
                        name: formData.name,
                        code: formData.code,
                        ue: formData.ue,
                        prof: formData.prof,
                        coeff: parseInt(formData.coeff),
                        status: formData.status,
                    };
                    if (selectedItem) {
                        await parametreService.updateEc(selectedItem.id, dataToSend);
                        setEcs(ecs.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, name: formData.name, code: formData.code, ue: formData.ue, prof: formData.prof, coeff: parseInt(formData.coeff), status: formData.status, description: formData.description }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createEc(dataToSend);
                        setEcs([...ecs, { ...response, id: response.id, name: response.name, code: response.code, ue: extractIdFromIri(response.ue), prof: extractIdFromIri(response.prof), coeff: response.coeff, status: response.status, description: response.description }]);
                    }
                    break;
                case 6: // Année Universitaire
                    dataToSend = { year: formData.year, current: formData.current };
                    if (selectedItem) {
                        await parametreService.updateYear(selectedItem.id, dataToSend);
                        setAnnees(annees.map(item =>
                            item.id === selectedItem.id
                                ? { ...item, year: formData.year, current: formData.current }
                                : item
                        ));
                    } else {
                        const response = await parametreService.createYear(dataToSend);
                        setAnnees([...annees, { ...response, id: response.id, year: response.year, current: response.current }]);
                    }
                    break;
                default:
                    break;
            }
            showToast('success', selectedItem ? 'Élément modifié avec succès' : 'Élément ajouté avec succès');
            setVisibleDialog(false);
        } catch (error) {
            showToast('error', error.message || 'Erreur lors de la sauvegarde');
            console.error(error);
        }
    };

    const getCurrentData = () => {
        switch (activeTab) {
            case 0: return mentions;
            case 1: return niveaux;
            case 2: return parcours;
            case 3: return semestres;
            case 4: return ues;
            case 5: return ecs;
            case 6: return annees;
            default: return [];
        }
    };

    const getParamName = () => {
        const names = [
            'Mention',
            'Niveau',
            'Parcours',
            'Semestre',
            "Unité d'Enseignement",
            'Élément Constitutif',
            'Année Universitaire'
        ];
        return names[activeTab];
    };

    const handleFormChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const renderHeader = () => (
        <div className="flex justify-between items-center">
            <h1 className='text-3xl font-normal text-gray-800'>{getParamName()}</h1>
            <div className='flex items-center justify-center space-x-5'>
                <Button
                    icon="pi pi-plus"
                    rounded
                    tooltip="Ajouter"
                    tooltipOptions={{ position: 'top' }}
                    onClick={handleAdd}
                />
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange}
                        placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        </div>
    );

    const actionBodyTemplate = (rowData) => (
        <div className='flex space-x-3 items-center justify-center'>
            <Button
                icon="pi pi-pen-to-square"
                rounded
                severity="success"
                tooltip="Modifier"
                tooltipOptions={{ position: 'top' }}
                onClick={() => handleEdit(rowData)}
            />
            <Button
                icon="pi pi-trash"
                rounded
                severity="danger"
                tooltip="Supprimer"
                tooltipOptions={{ position: 'top' }}
                onClick={() => handleDelete(rowData)}
            />
        </div>
    );

    const imageBodyTemplate = (rowData) => {
        const imageUrl = rowData.icon ? `${IMAGE_BASE_URL}${rowData.icon}` : 'default-icon.png';
        return (
            <div className="flex items-center justify-center">
                <div className="relative w-10 h-10">
                    <img
                        src={imageUrl}
                        className="absolute w-full h-full rounded-full object-cover border-2 border-white shadow-sm"
                        alt="icon"
                        onError={(e) => e.target.src = 'default-icon.png'}
                    />
                </div>
            </div>
        );
    };

    const nameBodyTemplate = (rowData) => (
        <span>{rowData.name || rowData.nom || rowData.year}</span>
    );

    const relationBodyTemplate = (rowData, field, options) => {
        if (!rowData[field]) return <span>-</span>;
        // Special case for prof to use profName if available
        if (field === 'prof') {
            return <span>{rowData.profName || getProfName(rowData.prof)}</span>;
        }
        let resolvedOptions = options;
        if (typeof options === 'string') {
            switch (options) {
                case 'mentionOptions':
                    resolvedOptions = mentionOptions;
                    break;
                case 'niveauOptions':
                    resolvedOptions = niveauOptions;
                    break;
                case 'parcoursOptions':
                    resolvedOptions = parcoursOptions;
                    break;
                case 'semestreOptions':
                    resolvedOptions = semestreOptions;
                    break;
                case 'ueOptions':
                    resolvedOptions = ueOptions;
                    break;
                case 'profOptions':
                    resolvedOptions = profOptions;
                    break;
                default:
                    resolvedOptions = [];
            }
        }
        const item = Array.isArray(resolvedOptions) ? resolvedOptions.find(opt => opt.value === rowData[field]) : null;
        return item ? <span>{item.label}</span> : <span>-</span>;
    };

    const booleanBodyTemplate = (rowData, field) => (
        <InputSwitch checked={rowData[field]} disabled />
    );

    const renderFormFields = () => {
        switch (activeTab) {
            case 0: // Mention
                return (
                    <div className='flex flex-col items-center'>
                        <div className="field">
                            <label htmlFor="name">Mention*</label>
                            <InputText id="name" value={formData.name || ''}
                                onChange={(e) => handleFormChange('name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="icon">Icône</label>
                            <FileUpload id="icon" mode="basic" name="iconFile"
                                accept="image/*" maxFileSize={1000000}
                                chooseLabel="Choisir une image"
                                onSelect={(e) => handleFormChange('iconFile', e.files[0])}
                            />
                        </div>
                    </div>
                );
            case 1: // Niveau
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="nom">Nom*</label>
                            <InputText id="nom" value={formData.nom || ''}
                                onChange={(e) => handleFormChange('nom', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="code">Code*</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="cycle">Cycle*</label>
                            <Dropdown id="cycle" options={cycleOptions}
                                value={formData.cycle}
                                onChange={(e) => handleFormChange('cycle', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="ordre">Ordre*</label>
                            <InputText id="ordre" value={formData.ordre || ''}
                                onChange={(e) => handleFormChange('ordre', parseInt(e.target.value) || 0)}
                                className="w-full" type="number" required />
                        </div>
                    </div>
                );
            case 2: // Parcours
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="name">Code*</label>
                            <InputText id="name" value={formData.name || ''}
                                onChange={(e) => handleFormChange('name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="full_name">Nom complet*</label>
                            <InputText id="full_name" value={formData.full_name || ''}
                                onChange={(e) => handleFormChange('full_name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="mention">Mention*</label>
                            <Dropdown id="mention" options={mentionOptions}
                                value={formData.mention}
                                onChange={(e) => handleFormChange('mention', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="niveau">Niveau*</label>
                            <Dropdown id="niveau" options={niveauOptions}
                                value={formData.niveau}
                                onChange={(e) => handleFormChange('niveau', e.value)}
                                className="w-full" required />
                        </div>
                    </div>
                );
            case 3: // Semestre
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="name">Nom*</label>
                            <InputText id="name" value={formData.name || ''}
                                onChange={(e) => handleFormChange('name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="code">Code</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" />
                        </div>
                        <div className="field">
                            <label htmlFor="niveau">Niveau*</label>
                            <Dropdown id="niveau" options={niveauOptions}
                                value={formData.niveau}
                                onChange={(e) => handleFormChange('niveau', e.value)}
                                className="w-full" required />
                        </div>
                    </div>
                );
            case 4: // Unité d'Enseignement
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="name">Numéro UE*</label>
                            <InputText id="name" value={formData.name || ''}
                                onChange={(e) => handleFormChange('name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="code">Code</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" />
                        </div>
                        <div className="field">
                            <label htmlFor="mention">Mention*</label>
                            <Dropdown id="mention" options={mentionOptions}
                                value={formData.mention}
                                onChange={(e) => handleFormChange('mention', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="semestre">Semestre*</label>
                            <Dropdown id="semestre" options={semestreOptions}
                                value={formData.semestre}
                                onChange={(e) => handleFormChange('semestre', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="parcours">Parcours*</label>
                            <MultiSelect id="parcours" options={parcoursOptions}
                                value={formData.parcours || []}
                                onChange={(e) => handleFormChange('parcours', e.value)}
                                className="w-full" />
                        </div>
                    </div>
                );
            case 5: // Élément Constitutif
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="name">Nom*</label>
                            <InputText id="name" value={formData.name || ''}
                                onChange={(e) => handleFormChange('name', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="code">Code*</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="ue">Unité d'enseignement*</label>
                            <Dropdown id="ue" options={ueOptions}
                                value={formData.ue}
                                onChange={(e) => handleFormChange('ue', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="prof">Professeur*</label>
                            <Dropdown id="prof" options={profOptions}
                                value={formData.prof}
                                onChange={(e) => handleFormChange('prof', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="coeff">Crédit*</label>
                            <InputText id="coeff" value={formData.coeff || ''}
                                onChange={(e) => handleFormChange('coeff', parseInt(e.target.value) || 0)}
                                className="w-full" type="number" required />
                        </div>
                        <div className="field flex items-center justify-start space-x-5">
                            <label htmlFor="status">Statut</label>
                            <InputSwitch id="status" checked={formData.status || false}
                                onChange={(e) => handleFormChange('status', e.value)} />
                        </div>
                    </div>
                );
            case 6: // Année Universitaire
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="year">Année scolaire*</label>
                            <InputText id="year" value={formData.year || ''}
                                onChange={(e) => handleFormChange('year', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field flex items-center justify-start space-x-5">
                            <label htmlFor="current">Actuel</label>
                            <InputSwitch id="current" checked={formData.current || false}
                                onChange={(e) => handleFormChange('current', e.value)} />
                        </div>
                    </div>
                );
            default:
                return null;
        }
    };

    return (
        <LayoutAdmin>
            <Toast ref={toast} />
            <ConfirmDialog />
            <div className="w-full items-center justify-center custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold text-gray-800 p-5'>Paramètres</h1>
                <div>
                    <TabView className='custom-tabview' activeIndex={activeTab} onTabChange={(e) => setActiveTab(e.index)}>
                        <TabPanel header="Mention" className='flex flex-col items-center'>
                            <DataTable value={mentions} paginator rows={7} dataKey="id" sortField="name" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="icon" header="Icône" body={imageBodyTemplate} style={{ width: '6rem' }} />
                                <Column field="name" header="Mention" sortable style={{ minWidth: '6rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '6rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Niveau" className='flex flex-col items-center'>
                            <DataTable value={niveaux} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="nom" header="Nom" sortable style={{ minWidth: '6rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '6rem' }} />
                                <Column field="cycle" header="Cycle" sortable style={{ minWidth: '6rem' }} />
                                <Column field="ordre" header="Ordre" sortable style={{ minWidth: '6rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '6rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Parcours" className='flex flex-col items-center'>
                            <DataTable value={parcours} paginator rows={7} dataKey="id" sortField="name" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="name" header="Code" sortable style={{ minWidth: '3rem' }} />
                                <Column field="full_name" header="Nom complet" sortable style={{ minWidth: '3rem' }} />
                                <Column field="mention" header="Mention" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'mention', 'mentionOptions')} sortable style={{ minWidth: '3rem' }} />
                                <Column field="niveau" header="Niveau" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'niveau', 'niveauOptions')} sortable style={{ minWidth: '3rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '3rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Semestre" className='flex flex-col'>
                            <DataTable value={semestres} paginator rows={7} dataKey="id" sortField="name" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="name" header="Nom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column field="niveau" header="Niveau" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'niveau', niveauOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Unité d'Enseignement" className='flex flex-col items-center'>
                            <DataTable value={ues} paginator rows={7} dataKey="id" sortField="name" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="name" header="N°" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'mention', mentionOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="semestre" header="Semestre" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'semestre', semestreOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="parcours" header="Parcours" body={(rowData) =>
                                    rowData.parcours.map(id => parcoursOptions.find(opt => opt.value === id)?.label || '-').join(', ')} sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Élément Constitutif" className='flex flex-col items-center'>
                            <DataTable value={ecs} paginator rows={7} dataKey="id" sortField="name" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="name" header="Nom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column field="ue" header="Unité d'enseignement" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'ue', ueOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column
                                    field="prof"
                                    header="Professeur"
                                    body={(rowData) => relationBodyTemplate(rowData, 'prof', profOptions)}
                                    sortable
                                    style={{ minWidth: '5rem' }}
                                />
                                <Column field="coeff" header="Crédit" sortable style={{ minWidth: '5rem' }} />
                                <Column field="status" header="Statut" body={(rowData) => booleanBodyTemplate(rowData, 'status')}
                                    style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Année Universitaire" className='flex flex-col items-center'>
                            <DataTable value={annees} paginator rows={7} dataKey="id" sortField="year" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="year" header="Année" style={{ minWidth: '5rem' }} />
                                <Column field="current" header="Actuel" body={(rowData) => booleanBodyTemplate(rowData, 'current')}
                                    style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>

            {/* Add/Edit Dialog */}
            <AnimatePresence>
                {visibleDialog && (
                    <Dialog
                        header={selectedItem ? `Modifier ${getParamName()}` : `Ajouter ${getParamName()}`}
                        visible={visibleDialog}
                        style={{ width: '40vw', backgroundColor: 'white' }}
                        contentStyle={{ backgroundColor: 'white' }}
                        onHide={() => setVisibleDialog(false)}
                        draggable={false}
                        modal
                        className="p-fluid"
                        blockScroll
                    >
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -20 }}
                            transition={{ duration: 0.2 }}
                        >
                            <form onSubmit={(e) => { e.preventDefault(); saveItem(); }}>
                                <div className="grid formgrid p-5 justify-center">
                                    {renderFormFields()}
                                </div>
                                <div className="flex justify-center gap-3">
                                    <Button
                                        type="button"
                                        severity="danger"
                                        label="Annuler"
                                        icon="pi pi-times"
                                        className='w-40'
                                        onClick={() => setVisibleDialog(false)}
                                    />
                                    <Button
                                        type="submit"
                                        label={selectedItem ? "Modifier" : "Ajouter"}
                                        icon={`pi ${selectedItem ? "pi-check" : "pi-plus"}`}
                                        className='w-40'
                                    />
                                </div>
                            </form>
                        </motion.div>
                    </Dialog>
                )}
            </AnimatePresence>
        </LayoutAdmin>
    );
};

export default Setting;