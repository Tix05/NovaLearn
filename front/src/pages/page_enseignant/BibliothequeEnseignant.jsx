import React, { useState, useEffect, useRef } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { FileUpload } from 'primereact/fileupload';
import { motion, AnimatePresence } from 'framer-motion';
import { Toast } from 'primereact/toast';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';
import { X } from 'lucide-react';
import { InputSwitch } from 'primereact/inputswitch';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { getBibliothequeItems, createBibliothequeItem, updateBibliothequeItem, uploadBibliothequeFile, deleteBibliothequeItem, getTeacherMentions } from '../../Services/bibliothequeEnseignantService';

export default function BibliothequeEnseignant() {
    const [data, setData] = useState([]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [categorieFilter, setCategorieFilter] = useState('Tous');
    const [ecFilter, setEcFilter] = useState('Tous');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [showEditDialog, setShowEditDialog] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [mentions, setMentions] = useState([]);
    const [semestres, setSemestres] = useState([]);
    const [parcours, setParcours] = useState([]);
    const [ues, setUes] = useState([]);
    const [ecs, setEcs] = useState([]);
    const [checked, setChecked] = useState(false);
    const toast = useRef(null);

    // Form data state
    const [formData, setFormData] = useState({
        mention: '',
        semestre: '',
        parcours: '',
        ue: '',
        ec: '',
        type: '',
        titre: '',
        file: null,
        existingFile: null,
    });

    // Types statiques
    const types = [
        { label: 'Veuillez choisir le type', value: '' },
        { label: 'Administration', value: 'administration' },
        { label: 'Sujet avec corrigé', value: 'sujet avec corrigé' },
        { label: 'Exercice', value: 'exercice' },
    ];

    // Options pour les filtres
    const mentionsFilterOptions = [
        { label: 'Mention', value: 'Tous' },
        ...Array.from(new Set(data.map(item => item.mentionName))).map(m => ({ label: m, value: m })),
    ];
    const niveauxFilterOptions = [
        { label: 'Niveau', value: 'Tous' },
        ...Array.from(new Set(data.map(item => item.niveauNom))).map(n => ({ label: n, value: n })),
    ];
    const categoriesFilterOptions = [
        { label: 'Catégorie', value: 'Tous' },
        ...Array.from(new Set(data.map(item => item.type))).map(c => ({ label: c, value: c })),
    ];
    const ecFilterOptions = [
        { label: 'EC', value: 'Tous' },
        ...Array.from(new Set(data.map(item => item.ecName))).map(e => ({ label: e, value: e })),
    ];

    // Charger les données initiales
    useEffect(() => {
        const fetchData = async () => {
            try {
                const items = await getBibliothequeItems();
                setData(items);

                const teacherMentions = await getTeacherMentions();
                setMentions([
                    { label: 'Veuillez choisir la mention', value: '' },
                    ...teacherMentions,
                ]);
            } catch (error) {
                console.error('Error fetching data:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors du chargement des données',
                    life: 3000
                });
            }
        };
        fetchData();
    }, []);

    const handleMentionChange = (e) => {
        const mentionId = e.value;
        setFormData((prev) => ({
            ...prev,
            mention: mentionId,
            semestre: '',
            parcours: '',
            ue: '',
            ec: '',
        }));

        const selectedMention = mentions.find((m) => m.value === mentionId);
        setSemestres(
            selectedMention && selectedMention.niveaux
                ? [
                    { label: 'Veuillez choisir le semestre', value: '' },
                    ...selectedMention.niveaux,
                ]
                : [{ label: 'Veuillez choisir le semestre', value: '' }]
        );
        setParcours(
            selectedMention && selectedMention.parcours
                ? [
                    { label: 'Veuillez choisir le parcours', value: '' },
                    ...selectedMention.parcours,
                ]
                : [{ label: 'Veuillez choisir le parcours', value: '' }]
        );
        setUes([{ label: 'Veuillez choisir l\'UE', value: '' }]);
        setEcs([{ label: 'Veuillez choisir l\'EC', value: '' }]);
    };

    const handleSemestreChange = (e) => {
        const semestreId = e.value;
        setFormData((prev) => ({
            ...prev,
            semestre: semestreId,
            ue: '',
            ec: '',
        }));

        const selectedSemestre = semestres.find((s) => s.value === semestreId);
        setUes(
            selectedSemestre && selectedSemestre.ues
                ? [
                    { label: 'Veuillez choisir l\'UE', value: '' },
                    ...selectedSemestre.ues,
                ]
                : [{ label: 'Veuillez choisir l\'UE', value: '' }]
        );
        setEcs([{ label: 'Veuillez choisir l\'EC', value: '' }]);
    };

    const handleUeChange = (e) => {
        const ueId = e.value;
        setFormData((prev) => ({
            ...prev,
            ue: ueId,
            ec: '',
        }));

        const selectedUe = ues.find((u) => u.value === ueId);
        setEcs(
            selectedUe && selectedUe.ecs
                ? [
                    { label: 'Veuillez choisir l\'EC', value: '' },
                    ...selectedUe.ecs,
                ]
                : [{ label: 'Veuillez choisir l\'EC', value: '' }]
        );
    };

    const handleParcoursChange = (e) => {
        const parcoursName = e.value;
        setFormData((prev) => ({
            ...prev,
            parcours: parcoursName,
        }));
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleFileChange = (e) => {
        setFormData((prev) => ({
            ...prev,
            file: e.files[0],
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        // Validation des champs requis
        if (!formData.mention || !formData.semestre || !formData.parcours || !formData.ue || !formData.ec || !formData.type || !formData.titre) {
            toast.current.show({
                severity: 'warn',
                summary: 'Attention',
                detail: 'Veuillez remplir tous les champs requis',
                life: 3000
            });
            return;
        }

        const submitAction = async () => {
            try {
                if (showEditDialog) {
                    // Prepare textual data for PATCH request
                    const textData = {
                        titre: formData.titre,
                        type: formData.type,
                        ec: String(formData.ec),
                        parcours: formData.parcours,
                        status: checked,
                    };

                    // Send textual updates
                    await updateBibliothequeItem(selectedItem.id, textData);

                    // Send file update if a new file is selected
                    if (formData.file) {
                        await uploadBibliothequeFile(selectedItem.id, formData.file);
                    }

                    toast.current.show({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Élément modifié avec succès',
                        life: 3000
                    });
                } else {
                    if (!formData.file) {
                        toast.current.show({
                            severity: 'warn',
                            summary: 'Attention',
                            detail: 'Un fichier est requis pour la création',
                            life: 3000
                        });
                        return;
                    }

                    const formDataToSend = new FormData();
                    formDataToSend.append('titre', formData.titre);
                    formDataToSend.append('type', formData.type);
                    formDataToSend.append('ec', String(formData.ec));
                    formDataToSend.append('parcours', formData.parcours);
                    formDataToSend.append('file', formData.file);
                    formDataToSend.append('status', checked ? '1' : '0');

                    await createBibliothequeItem(formDataToSend);
                    toast.current.show({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Élément créé avec succès',
                        life: 3000
                    });
                }

                const updatedItems = await getBibliothequeItems();
                setData(updatedItems);
                setShowCreateDialog(false);
                setShowEditDialog(false);
                setFormData({
                    mention: '',
                    semestre: '',
                    parcours: '',
                    ue: '',
                    ec: '',
                    type: '',
                    titre: '',
                    file: null,
                    existingFile: null,
                });
                setChecked(false);
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Une erreur est survenue',
                    life: 3000
                });
            }
        };

        if (showEditDialog) {
            confirmDialog({
                message: 'Êtes-vous sûr de vouloir modifier cet élément ?',
                header: 'Confirmation de modification',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'Oui',
                rejectLabel: 'Non',
                accept: submitAction,
                reject: () => toast.current.show({
                    severity: 'info',
                    summary: 'Annulé',
                    detail: 'Modification annulée',
                    life: 3000
                }),
            });
        } else {
            submitAction();
        }
    };

    const handleEdit = (rowData) => {
        if (rowData.source !== 'bibliotheque') return;
        setSelectedItem(rowData);

        const selectedMention = mentions.find((m) => m.label === rowData.mentionName);
        const mentionId = selectedMention ? selectedMention.value : '';

        const selectedSemestre = selectedMention?.niveaux.find((s) =>
            s.ues.some((u) => u.ecs.some((ec) => ec.label === rowData.ecName))
        );
        const semestreId = selectedSemestre ? selectedSemestre.value : '';

        const selectedUe = selectedSemestre?.ues.find((u) => u.ecs.some((ec) => ec.label === rowData.ecName));
        const ueId = selectedUe ? selectedUe.value : '';

        const selectedEc = selectedUe?.ecs.find((ec) => ec.label === rowData.ecName);
        const ecId = selectedEc ? selectedEc.value : '';

        const selectedParcours = selectedMention?.parcours.find((p) => p.label === rowData.parcoursName);
        const parcoursName = selectedParcours ? selectedParcours.value : '';

        // Vérifier que les champs requis sont présents
        if (!mentionId || !semestreId || !ueId || !ecId || !parcoursName || !rowData.type || !rowData.titre) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de charger les données pour la modification. Vérifiez les données de l\'élément.',
                life: 5000,
            });
            return;
        }

        setFormData({
            mention: mentionId,
            semestre: semestreId,
            parcours: parcoursName,
            ue: ueId,
            ec: ecId,
            type: rowData.type,
            titre: rowData.titre,
            file: null,
            existingFile: rowData.fichier,
        });

        if (selectedMention) {
            setSemestres([
                { label: 'Veuillez choisir le semestre', value: '' },
                ...selectedMention.niveaux,
            ]);
            setParcours([
                { label: 'Veuillez choisir le parcours', value: '' },
                ...selectedMention.parcours,
            ]);
            setUes(
                selectedSemestre
                    ? [
                        { label: 'Veuillez choisir l\'UE', value: '' },
                        ...selectedSemestre.ues,
                    ]
                    : [{ label: 'Veuillez choisir l\'UE', value: '' }]
            );
            setEcs(
                selectedUe
                    ? [
                        { label: 'Veuillez choisir l\'EC', value: '' },
                        ...selectedUe.ecs,
                    ]
                    : [{ label: 'Veuillez choisir l\'EC', value: '' }]
            );
        }

        setChecked(rowData.isPublished);
        setShowEditDialog(true);
    };

    const handleDelete = (id, source) => {
        if (source !== 'bibliotheque') return;

        confirmDialog({
            message: 'Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: async () => {
                try {
                    await deleteBibliothequeItem(id);
                    const updatedItems = await getBibliothequeItems();
                    setData(updatedItems);
                    toast.current.show({
                        severity: 'success',
                        summary: 'Succès',
                        detail: 'Élément supprimé avec succès',
                        life: 3000
                    });
                } catch (error) {
                    console.error('Erreur lors de la suppression:', error);
                    toast.current.show({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: 'Une erreur est survenue lors de la suppression',
                        life: 3000
                    });
                }
            },
            reject: () => toast.current.show({
                severity: 'info',
                summary: 'Annulé',
                detail: 'Suppression annulée',
                life: 3000
            }),
        });
    };

    const handleDownload = (filename) => {
        const link = document.createElement('a');
        link.href = filename;
        link.download = filename.split('/').pop();
        link.click();
    };

    const filteredData = data.filter(
        (item) =>
            (mentionFilter === 'Tous' || item.mentionName === mentionFilter) &&
            (niveauFilter === 'Tous' || item.niveauNom === niveauFilter) &&
            (categorieFilter === 'Tous' || item.type === categorieFilter) &&
            (ecFilter === 'Tous' || item.ecName === ecFilter) &&
            (item.titre.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
                item.mentionName.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
                item.niveauNom.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
                item.ecName.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
                item.type.toLowerCase().includes(globalFilterValue.toLowerCase()))
    );

    const dropdownTemplate = (option, props) => {
        if (option) {
            return (
                <div className="flex align-items-center">
                    <div>{option.label}</div>
                </div>
            );
        }
        return <span>{props.placeholder}</span>;
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className="flex items-center gap-x-2">
                <Button
                    icon="pi pi-download"
                    rounded
                    severity="secondary"
                    onClick={() => handleDownload(rowData.fichier)}
                />
                <Button
                    icon="pi pi-eye"
                    rounded
                    severity="success"
                    onClick={() => handleDownload(rowData.fichier)}
                />
                {rowData.source === 'bibliotheque' && (
                    <>
                        <Button
                            icon="pi pi-pencil"
                            rounded
                            severity="info"
                            onClick={() => handleEdit(rowData)}
                        />
                        <Button
                            icon="pi pi-trash"
                            rounded
                            severity="danger"
                            onClick={() => handleDelete(rowData.id, rowData.source)}
                        />
                    </>
                )}
            </div>
        );
    };

    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className="text-3xl p-5 font-semibold">Bibliothèque</h1>
                <div className="grid md:grid-cols-2 grid-cols-1 justify-center gap-3 items-center">
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText
                            value={globalFilterValue}
                            onChange={(e) => setGlobalFilterValue(e.target.value)}
                            placeholder="Rechercher..."
                            className="custom-input"
                        />
                    </IconField>
                    <div className="flex flex-wrap items-center justify-center gap-3 bibliotheque-dropdown">
                        <Dropdown
                            value={mentionFilter}
                            onChange={(e) => setMentionFilter(e.value)}
                            options={mentionsFilterOptions}
                            optionLabel="label"
                            placeholder="Mention"
                            filter
                            valueTemplate={dropdownTemplate}
                            itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                        <Dropdown
                            value={niveauFilter}
                            onChange={(e) => setNiveauFilter(e.value)}
                            options={niveauxFilterOptions}
                            optionLabel="label"
                            placeholder="Niveau"
                            filter
                            valueTemplate={dropdownTemplate}
                            itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                        <Dropdown
                            value={categorieFilter}
                            onChange={(e) => setCategorieFilter(e.value)}
                            options={categoriesFilterOptions}
                            optionLabel="label"
                            placeholder="Catégorie"
                            filter
                            valueTemplate={dropdownTemplate}
                            itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                        <Dropdown
                            value={ecFilter}
                            onChange={(e) => setEcFilter(e.value)}
                            options={ecFilterOptions}
                            optionLabel="label"
                            placeholder="EC"
                            filter
                            valueTemplate={dropdownTemplate}
                            itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                    </div>
                    <Button
                        label="Créer"
                        icon="pi pi-file-plus"
                        severity="success"
                        onClick={() => setShowCreateDialog(true)}
                    />
                </div>
            </div>
        );
    };

    return (
        <LayoutEnseignant>
            <Toast ref={toast} />
            <ConfirmDialog />
            <div className="relative">
                <DataTable
                    value={filteredData}
                    paginator
                    rows={5}
                    dataKey="id"
                    sortField="titre"
                    sortOrder={1}
                    header={renderHeader()}
                    emptyMessage="Aucune donnée trouvée."
                >
                    <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                    <Column field="mentionName" header="Mention" sortable style={{ minWidth: '10rem' }} />
                    <Column field="niveauNom" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                    <Column field="ecName" header="EC" sortable style={{ minWidth: '10rem' }} />
                    <Column field="type" header="Catégorie" sortable style={{ minWidth: '10rem' }} />
                    <Column body={actionBodyTemplate} header="Action" sortable style={{ minWidth: '10rem' }} />
                </DataTable>

                <AnimatePresence>
                    {(showCreateDialog || showEditDialog) && (
                        <div
                            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                            onClick={() => {
                                setShowCreateDialog(false);
                                setShowEditDialog(false);
                            }}
                        >
                            <motion.div
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                exit={{ opacity: 0, scale: 0.9 }}
                                transition={{ duration: 0.2 }}
                                className="bg-white rounded-md shadow-xl w-full max-w-5xl"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="bg-teal-500 text-white px-6 py-4 rounded-t-md flex justify-between items-center sticky top-0 z-10">
                                    <h1 className="text-xl font-semibold">
                                        {showEditDialog ? 'Modifier Bibliothèque' : 'Créer Bibliothèque'}
                                    </h1>
                                    <button
                                        className="text-white hover:bg-teal-600 rounded-full p-1"
                                        onClick={() => {
                                            setShowCreateDialog(false);
                                            setShowEditDialog(false);
                                        }}
                                    >
                                        <X size={24} />
                                    </button>
                                </div>

                                <form onSubmit={handleSubmit} className="p-6 space-y-6 overflow-y-auto h-[80vh] custom-scrollbar">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-5">
                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Mention*
                                            </label>
                                            <Dropdown
                                                name="mention"
                                                value={formData.mention}
                                                onChange={handleMentionChange}
                                                options={mentions}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir la mention"
                                                className="w-full"
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Semestre*
                                            </label>
                                            <Dropdown
                                                name="semestre"
                                                value={formData.semestre}
                                                onChange={handleSemestreChange}
                                                options={semestres}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le semestre"
                                                className="w-full"
                                                disabled={!formData.mention}
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Parcours*
                                            </label>
                                            <Dropdown
                                                name="parcours"
                                                value={formData.parcours}
                                                onChange={handleParcoursChange}
                                                options={parcours}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le parcours"
                                                className="w-full"
                                                disabled={!formData.mention}
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                UE*
                                            </label>
                                            <Dropdown
                                                name="ue"
                                                value={formData.ue}
                                                onChange={handleUeChange}
                                                options={ues}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir l'UE"
                                                className="w-full"
                                                disabled={!formData.semestre}
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                EC*
                                            </label>
                                            <Dropdown
                                                name="ec"
                                                value={formData.ec}
                                                onChange={handleChange}
                                                options={ecs}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir l'EC"
                                                className="w-full"
                                                disabled={!formData.ue}
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Type*
                                            </label>
                                            <Dropdown
                                                name="type"
                                                value={formData.type}
                                                onChange={handleChange}
                                                options={types}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le type"
                                                className="w-full"
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className="w-60">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Titre*
                                            </label>
                                            <InputText
                                                name="titre"
                                                value={formData.titre}
                                                onChange={handleChange}
                                                className="w-full"
                                                placeholder="Entrez le titre"
                                            />
                                        </div>

                                        <div className="flex justify-between w-full">
                                            <div className="flex flex-col w-full">
                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                    Fichier* {showEditDialog && formData.existingFile ? '(Fichier existant)' : ''}
                                                </label>
                                                {showEditDialog && formData.existingFile && (
                                                    <div className="mb-2">
                                                        <p className="text-sm text-gray-600">
                                                            Fichier actuel :{' '}
                                                            <a
                                                                href={formData.existingFile}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="text-blue-600 hover:underline"
                                                            >
                                                                {formData.existingFile.split('/').pop()}
                                                            </a>
                                                        </p>
                                                    </div>
                                                )}
                                                <FileUpload
                                                    mode="basic"
                                                    name="file"
                                                    accept=".pdf,.doc,.docx"
                                                    maxFileSize={10000000}
                                                    onSelect={handleFileChange}
                                                    chooseLabel={showEditDialog && formData.existingFile ? 'Remplacer le fichier' : 'Choisir un fichier'}
                                                    className="w-full"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                    Publier
                                                </label>
                                                <InputSwitch checked={checked} onChange={(e) => setChecked(e.value)} />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex justify-end pt-4">
                                        <Button
                                            type="button"
                                            label="Annuler"
                                            severity="secondary"
                                            onClick={() => {
                                                setShowCreateDialog(false);
                                                setShowEditDialog(false);
                                            }}
                                            className="mr-2"
                                        />
                                        <Button type="submit" label="Enregistrer" severity="success" />
                                    </div>
                                </form>
                            </motion.div>
                        </div>
                    )}
                </AnimatePresence>
            </div>
        </LayoutEnseignant>
    );
}