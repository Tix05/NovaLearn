import React, { useState, useRef } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { FileUpload } from 'primereact/fileupload';
import { InputTextarea } from 'primereact/inputtextarea';
import { motion, AnimatePresence } from 'framer-motion';
import LayoutAdmin from '../../components/LayoutAdmin';
import { X } from 'lucide-react';
import { InputSwitch } from "primereact/inputswitch";
import { Dialog } from 'primereact/dialog';
import { Toast } from 'primereact/toast';

export default function BibliothequeAdmin() {
    const [data, setData] = useState([
        { id: 1, titre: 'Livre 1', mention: 'Mathématiques', niveau: 'Licence 1', categorie: 'Science' },
        { id: 2, titre: 'Livre 2', mention: 'Physique', niveau: 'Master 2', categorie: 'Science' },
        { id: 3, titre: 'Livre 3', mention: 'Informatique', niveau: 'Licence 3', categorie: 'Technologie' },
    ]);

    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [categorieFilter, setCategorieFilter] = useState('Tous');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [availableSemestres, setAvailableSemestres] = useState([]);
    const [checked, setChecked] = useState(false);
    const [deleteDialogVisible, setDeleteDialogVisible] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const toast = useRef(null);

    // Form data state
    const [formData, setFormData] = useState({
        mention: '',
        niveau: '',
        semestre: '',
        parcours: '',
        ec: '',
        type: '',
        titre: '',
        description: '',
        file: null
    });

    const getAvailableSemestres = (niveau) => {
        switch (niveau) {
            case 'l1':
                return [
                    { label: 'Semestre 1', value: 's1' },
                    { label: 'Semestre 2', value: 's2' }
                ];
            case 'l2':
                return [
                    { label: 'Semestre 3', value: 's3' },
                    { label: 'Semestre 4', value: 's4' }
                ];
            case 'l3':
                return [
                    { label: 'Semestre 5', value: 's5' },
                    { label: 'Semestre 6', value: 's6' }
                ];
            case 'm1':
                return [
                    { label: 'Semestre 7', value: 's7' },
                    { label: 'Semestre 8', value: 's8' }
                ];
            case 'm2':
                return [
                    { label: 'Semestre 9', value: 's9' },
                    { label: 'Semestre 10', value: 's10' }
                ];
            default:
                return [];
        }
    };

    const mentions = [{ label: 'Mention', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.mention))).map(m => ({ label: m, value: m }))];
    const niveaux = [{ label: 'Niveau', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.niveau))).map(n => ({ label: n, value: n }))];
    const categories = [{ label: 'Catégorie', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.categorie))).map(c => ({ label: c, value: c }))];

    // Options for form dropdowns
    const formMentions = [
        { label: 'Veuillez choisir la mention', value: '' },
        { label: 'Informatique', value: 'informatique' },
        { label: 'Mathématiques', value: 'mathematiques' },
        { label: 'Physique', value: 'physique' }
    ];

    const formNiveaux = [
        { label: 'Veuillez choisir le niveau', value: '' },
        { label: 'L1', value: 'l1' },
        { label: 'L2', value: 'l2' },
        { label: 'L3', value: 'l3' },
        { label: 'Master 1', value: 'm1' },
        { label: 'Master 2', value: 'm2' }
    ];

    const parcours = [
        { label: 'Veuillez choisir le parcours', value: '' },
        { label: 'Génie Logiciel', value: 'gl' },
        { label: 'ASI', value: 'asi' }
    ];

    const ecs = [
        { label: 'Veuillez choisir l\'EC', value: '' },
        { label: 'Anglais', value: 'anglais' },
        { label: 'Français', value: 'francais' },
        { label: 'Algorithmique', value: 'algorithmique' },
        { label: 'Base de données', value: 'bdd' }
    ];

    const types = [
        { label: 'Veuillez choisir le type', value: '' },
        { label: 'Administration', value: 'administration' },
        { label: 'Cours', value: 'cours' },
        { label: 'TD', value: 'td' },
        { label: 'TP', value: 'tp' }
    ];

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleNiveauChange = (e) => {
        const niveau = e.value;
        setFormData(prev => ({
            ...prev,
            niveau,
            semestre: '' // Réinitialise le semestre quand on change de niveau
        }));

        // Met à jour les semestres disponibles
        setAvailableSemestres(getAvailableSemestres(niveau));
    };

    const handleFileChange = (e) => {
        setFormData(prev => ({
            ...prev,
            file: e.files[0]
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        // Création d'un nouvel élément
        const newItem = {
            id: data.length > 0 ? Math.max(...data.map(item => item.id)) + 1 : 1,
            titre: formData.titre,
            mention: formData.mention,
            niveau: formData.niveau,
            categorie: 'Nouvelle catégorie' // Vous pouvez adapter ceci
        };

        // Ajout à la liste
        setData([...data, newItem]);

        // Réinitialisation du formulaire
        setFormData({
            mention: '',
            niveau: '',
            semestre: '',
            parcours: '',
            ec: '',
            type: '',
            titre: '',
            description: '',
            file: null
        });

        // Fermeture du dialogue
        setShowCreateDialog(false);

        // Affichage du toast
        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: 'Le document a été ajouté avec succès',
            life: 3000
        });
    };

    const confirmDelete = (item) => {
        setSelectedItem(item);
        setDeleteDialogVisible(true);
    };

    const deleteItem = () => {
        setData(data.filter(item => item.id !== selectedItem.id));
        setDeleteDialogVisible(false);

        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: 'Le document a été supprimé avec succès',
            life: 3000
        });
    };

    const handleDownload = (filename) => {
        const link = document.createElement('a');
        link.href = `/path/to/supports/${filename}`;
        link.download = filename;
        link.click();

        toast.current.show({
            severity: 'info',
            summary: 'Téléchargement',
            detail: 'Le téléchargement a commencé',
            life: 3000
        });
    };

    const filteredData = data.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (categorieFilter === 'Tous' || item.categorie === categorieFilter) &&
        (item.titre.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.mention.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.niveau.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.categorie.toLowerCase().includes(globalFilterValue.toLowerCase())));

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
            <div className='flex items-center gap-x-2'>
                <Button icon="pi pi-download" rounded severity="secondary" onClick={() => handleDownload(rowData.titre)} />
                <Button icon="pi pi-eye" rounded severity="success" onClick={() => handleDownload(rowData.titre)} />
                <Button icon="pi pi-trash" rounded severity="danger" onClick={() => confirmDelete(rowData)} />
            </div>
        );
    };

    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className='text-3xl font-normal'>Bibliothèque</h1>
                <div className='flex justify-center space-x-4 items-center'>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                    </IconField>

                    <div className="flex space-x-2 bibliotheque-dropdown">
                        <Dropdown value={mentionFilter} onChange={(e) => setMentionFilter(e.value)} options={mentions}
                            optionLabel="label" placeholder="Mention"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />

                        <Dropdown value={niveauFilter} onChange={(e) => setNiveauFilter(e.value)} options={niveaux}
                            optionLabel="label" placeholder="Niveau"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />

                        <Dropdown value={categorieFilter} onChange={(e) => setCategorieFilter(e.value)} options={categories}
                            optionLabel="label" placeholder="Catégorie"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                        <Button
                            label="Créer"
                            icon="pi pi-file-plus"
                            severity='success'
                            onClick={() => setShowCreateDialog(true)}
                        />
                    </div>
                </div>
            </div>
        );
    };

    const deleteDialogFooter = (
        <>
            <Button label="Non" icon="pi pi-times" onClick={() => setDeleteDialogVisible(false)} className="p-button-text" />
            <Button label="Oui" icon="pi pi-check" onClick={deleteItem} severity="danger" />
        </>
    );

    return (
        <LayoutAdmin>
            <Toast ref={toast} />
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
                    <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                    <Column field="categorie" header="Catégorie" sortable style={{ minWidth: '10rem' }} />
                    <Column body={actionBodyTemplate} header="Action" sortable style={{ minWidth: '10rem' }} />
                </DataTable>

                {/* Dialog de suppression */}
                <Dialog
                    visible={deleteDialogVisible}
                    style={{ width: '450px' }}
                    header="Confirmation"
                    modal
                    footer={deleteDialogFooter}
                    onHide={() => setDeleteDialogVisible(false)}
                >
                    <div className="flex align-items-center justify-content-center">
                        <i className="pi pi-exclamation-triangle mr-3" style={{ fontSize: '2rem', color: '#f8bb86' }} />
                        {selectedItem && (
                            <span>
                                Êtes-vous sûr de vouloir supprimer <b>{selectedItem.titre}</b> ?
                            </span>
                        )}
                    </div>
                </Dialog>

                {/* Modal de création avec animation Framer Motion */}
                <AnimatePresence>
                    {showCreateDialog && (
                        <div
                            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                            onClick={() => setShowCreateDialog(false)}
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
                                    <h1 className="text-xl font-semibold">Créer Bibliothèque</h1>
                                    <button
                                        className="text-white hover:bg-teal-600 rounded-full p-1"
                                        onClick={() => setShowCreateDialog(false)}
                                    >
                                        <X size={24} />
                                    </button>
                                </div>

                                <form onSubmit={handleSubmit} className="p-6 space-y-6 overflow-y-auto h-[80vh] custom-scrollbar">
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-5">
                                        <div className='w-60'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Mention*
                                            </label>
                                            <Dropdown
                                                name="mention"
                                                value={formData.mention}
                                                onChange={handleChange}
                                                options={formMentions}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir la mention"
                                                className="w-full"
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className='w-60'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Niveau*
                                            </label>
                                            <Dropdown
                                                name="niveau"
                                                value={formData.niveau}
                                                onChange={handleNiveauChange}
                                                options={formNiveaux}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le niveau"
                                                className="w-full"
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className='w-68'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Semestre*
                                            </label>
                                            <Dropdown
                                                name="semestre"
                                                value={formData.semestre}
                                                onChange={handleChange}
                                                options={[
                                                    { label: 'Veuillez choisir le semestre', value: '' },
                                                    ...availableSemestres
                                                ]}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le semestre"
                                                className="w-full"
                                                disabled={!formData.niveau}
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className='w-60'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Parcours*
                                            </label>
                                            <Dropdown
                                                name="parcours"
                                                value={formData.parcours}
                                                onChange={handleChange}
                                                options={parcours}
                                                optionLabel="label"
                                                placeholder="Veuillez choisir le parcours"
                                                className="w-full"
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className='w-60'>
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
                                                filter
                                                showFilterClear
                                                filterPlaceholder="Rechercher..."
                                                panelClassName="font-poppins text-sm"
                                            />
                                        </div>

                                        <div className='w-60'>
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

                                        <div className="w-full">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Description du document*
                                            </label>
                                            <InputTextarea
                                                name="description"
                                                value={formData.description}
                                                onChange={handleChange}
                                                rows={6}
                                                className="w-full"
                                                placeholder="Entrez la description"
                                                style={{ width: '100%' }}
                                            />
                                        </div>

                                        <div className='flex justify-between w-full'>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                                    Fichier*
                                                </label>
                                                <FileUpload
                                                    mode="basic"
                                                    name="file"
                                                    url="/api/upload"
                                                    accept=".pdf,.doc,.docx,.ppt,.pptx"
                                                    maxFileSize={10000000}
                                                    onUpload={handleFileChange}
                                                    chooseLabel="Choisir un fichier"
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
                                            onClick={() => setShowCreateDialog(false)}
                                            className="mr-2"
                                        />
                                        <Button
                                            type="submit"
                                            label="Enregistrer"
                                            severity="success"
                                        />
                                    </div>
                                </form>
                            </motion.div>
                        </div>
                    )}
                </AnimatePresence>
            </div>
        </LayoutAdmin>
    );
}