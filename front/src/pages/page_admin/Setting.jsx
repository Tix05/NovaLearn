import React, { useState, useRef } from 'react';
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
import { FileUpload } from 'primereact/fileupload';
import { InputSwitch } from 'primereact/inputswitch';
import { motion, AnimatePresence } from 'framer-motion';

const Setting = () => {
    const toast = useRef(null);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [visibleDialog, setVisibleDialog] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [activeTab, setActiveTab] = useState(0);
    const [formData, setFormData] = useState({});

    // Données initiales avec relations entre les entités
    const [mentions, setMentions] = useState([
        { id: 1, icon: 'path/to/image1.jpg', mention: 'Informatique' }
    ]);

    const [niveaux, setNiveaux] = useState([
        { id: 1, nom: 'Licence 1', code: 'L1', frais: 50000 }
    ]);

    const [parcours, setParcours] = useState([
        { id: 1, nom: 'Informatique Fondamentale', mention: 1, niveau: 1, code: 'IF' }
    ]);

    const [semestres, setSemestres] = useState([
        { id: 1, nom: 'Semestre 1', code: 'S1' }
    ]);

    const [ues, setUes] = useState([
        { id: 1, numero: 'UE1', mention: 1, parcours: 1, semestre: 1, code: 'UE1-IF' }
    ]);

    const [ecs, setEcs] = useState([
        { id: 1, nom: 'Algorithmique', UE: 1, professeur: 'Dr. Smith', code: 'ALGO', credit: 5, statut: true }
    ]);

    const [annees, setAnnees] = useState([
        { id: 1, year: '2023-2024', actuel: true }
    ]);

    const mentionOptions = mentions.map(m => ({ label: m.mention, value: m.id }));
    const niveauOptions = niveaux.map(n => ({ label: n.nom, value: n.id }));
    const parcoursOptions = parcours.map(p => ({ label: p.nom, value: p.id }));
    const semestreOptions = semestres.map(s => ({ label: s.nom, value: s.id }));
    const ueOptions = ues.map(u => ({ label: `UE${u.numero} - ${getMentionName(u.mention)}`, value: u.id }));

    function getMentionName(id) {
        const mention = mentions.find(m => m.id === id);
        return mention ? mention.mention : '';
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
        setFormData({ ...rowData });
        setVisibleDialog(true);
    };

    const handleDelete = (rowData) => {
        confirmDialog({
            message: 'Êtes-vous sûr de vouloir supprimer cet élément?',
            header: 'Confirmation',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: () => deleteItem(rowData),
            reject: () => { }
        });
    };

    const deleteItem = (item) => {
        switch (activeTab) {
            case 0: setMentions(mentions.filter(i => i.id !== item.id)); break;
            case 1: setNiveaux(niveaux.filter(i => i.id !== item.id)); break;
            case 2: setParcours(parcours.filter(i => i.id !== item.id)); break;
            case 3: setSemestres(semestres.filter(i => i.id !== item.id)); break;
            case 4: setUes(ues.filter(i => i.id !== item.id)); break;
            case 5: setEcs(ecs.filter(i => i.id !== item.id)); break;
            case 6: setAnnees(annees.filter(i => i.id !== item.id)); break;
            default: break;
        }
        showToast('success', 'Élément supprimé avec succès');
    };

    const saveItem = () => {
        try {
            if (selectedItem) {
                // Modification
                switch (activeTab) {
                    case 0:
                        setMentions(mentions.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 1:
                        setNiveaux(niveaux.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 2:
                        setParcours(parcours.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 3:
                        setSemestres(semestres.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 4:
                        setUes(ues.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 5:
                        setEcs(ecs.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    case 6:
                        setAnnees(annees.map(item =>
                            item.id === selectedItem.id ? formData : item
                        ));
                        break;
                    default: break;
                }
                showToast('success', 'Élément modifié avec succès');
            } else {
                // Ajout
                const newId = Math.max(0, ...getCurrentData().map(item => item.id)) + 1;
                const newItem = { ...formData, id: newId };

                switch (activeTab) {
                    case 0: setMentions([...mentions, newItem]); break;
                    case 1: setNiveaux([...niveaux, newItem]); break;
                    case 2: setParcours([...parcours, newItem]); break;
                    case 3: setSemestres([...semestres, newItem]); break;
                    case 4: setUes([...ues, newItem]); break;
                    case 5: setEcs([...ecs, newItem]); break;
                    case 6: setAnnees([...annees, newItem]); break;
                    default: break;
                }
                showToast('success', 'Élément ajouté avec succès');
            }

            setVisibleDialog(false);
        } catch (error) {
            showToast('error', 'Une erreur est survenue');
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
            "Mention", "Niveau", "Parcours", "Semestre",
            "Unité d'Enseignement", "Elément Constitutif", "Année Universitaire"
        ];
        return names[activeTab];
    };

    const handleFormChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal text-gray-800'>{getParamName()}</h1>
                <div className='flex items-center justify-center space-x-5'>
                    <Button icon="pi pi-plus" rounded tooltip="Ajouter"
                        tooltipOptions={{ position: 'top' }} onClick={handleAdd} />
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange}
                            placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                </div>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex space-x-3 items-center justify-center'>
                <Button icon="pi pi-pen-to-square" rounded severity="success" tooltip="Modifier"
                    tooltipOptions={{ position: 'top' }} onClick={() => handleEdit(rowData)} />
                <Button icon="pi pi-trash" rounded severity="danger" tooltip="Supprimer"
                    tooltipOptions={{ position: 'top' }} onClick={() => handleDelete(rowData)} />
            </div>
        );
    };

    const imageBodyTemplate = (rowData) => {
        return (
            <div className="flex items-center justify-center">
                <div className="relative w-10 h-10">
                    <img
                        src={rowData.icon || 'default-icon.png'}
                        className="absolute w-full h-full rounded-full object-cover border-2 border-white shadow-sm"
                        alt="icon"
                        onError={(e) => e.target.src = 'default-icon.png'}
                    />
                </div>
            </div>
        );
    };

    const nameBodyTemplate = (rowData) => {
        return <span>{rowData.nom || rowData.mention || rowData.numero || rowData.year}</span>;
    };

    const relationBodyTemplate = (rowData, field, options) => {
        const item = options.find(opt => opt.value === rowData[field]);
        return item ? <span>{item.label}</span> : <span>-</span>;
    };

    const booleanBodyTemplate = (rowData, field) => {
        return <InputSwitch checked={rowData[field]} disabled />;
    };

    const renderFormFields = () => {
        switch (activeTab) {
            case 0: // Mention
                return (
                    <div className='flex flex-col items-center'>
                        <div className="field">
                            <label htmlFor="mention">Mention*</label>
                            <InputText id="mention" value={formData.mention || ''}
                                onChange={(e) => handleFormChange('mention', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="icon">Icône</label>
                            <FileUpload id="icon" mode="basic" name="icon"
                                accept="image/*" maxFileSize={1000000}
                                chooseLabel="Choisir une image"
                                onSelect={(e) => handleFormChange('icon', URL.createObjectURL(e.files[0]))} />
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
                            <label htmlFor="frais">Frais d'inscription*</label>
                            <InputText id="frais" value={formData.frais || ''}
                                onChange={(e) => handleFormChange('frais', parseInt(e.target.value) || 0)}
                                className="w-full" required type="number" />
                        </div>
                    </div>
                );
            case 2: // Parcours
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="nom">Nom*</label>
                            <InputText id="nom" value={formData.nom || ''}
                                onChange={(e) => handleFormChange('nom', e.target.value)}
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
                        <div className="field">
                            <label htmlFor="code">Code*</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" required />
                        </div>
                    </div>
                );
            case 3: // Semestre
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
                    </div>
                );
            case 4: // Unité d'Enseignement
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="numero">Numéro UE*</label>
                            <InputText id="numero" value={formData.numero || ''}
                                onChange={(e) => handleFormChange('numero', e.target.value)}
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
                            <label htmlFor="parcours">Parcours*</label>
                            <Dropdown id="parcours" options={parcoursOptions}
                                value={formData.parcours}
                                onChange={(e) => handleFormChange('parcours', e.value)}
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
                            <label htmlFor="code">Code*</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" required />
                        </div>
                    </div>
                );
            case 5: // Élément Constitutif
                return (
                    <div className='flex flex-col'>
                        <div className="field">
                            <label htmlFor="nom">Nom*</label>
                            <InputText id="nom" value={formData.nom || ''}
                                onChange={(e) => handleFormChange('nom', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="UE">Unité d'enseignement*</label>
                            <Dropdown id="UE" options={ueOptions}
                                value={formData.UE}
                                onChange={(e) => handleFormChange('UE', e.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="professeur">Professeur</label>
                            <InputText id="professeur" value={formData.professeur || ''}
                                onChange={(e) => handleFormChange('professeur', e.target.value)}
                                className="w-full" />
                        </div>
                        <div className="field">
                            <label htmlFor="code">Code*</label>
                            <InputText id="code" value={formData.code || ''}
                                onChange={(e) => handleFormChange('code', e.target.value)}
                                className="w-full" required />
                        </div>
                        <div className="field">
                            <label htmlFor="credit">Crédit*</label>
                            <InputText id="credit" value={formData.credit || ''}
                                onChange={(e) => handleFormChange('credit', parseInt(e.target.value) || 0)}
                                className="w-full" required type="number" />
                        </div>
                        <div className="field flex items-center justify-start space-x-5">
                            <label htmlFor="statut">Statut</label>
                            <InputSwitch id="statut" checked={formData.statut || false}
                                onChange={(e) => handleFormChange('statut', e.value)} />
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
                            <label htmlFor="actuel">Actuel</label>
                            <InputSwitch id="actuel" checked={formData.actuel || false}
                                onChange={(e) => handleFormChange('actuel', e.value)} />
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
                <h1 className='text-4xl font-semibold text-gray-800 p-5'>Paramètre</h1>
                <div>
                    <TabView className='custom-tabview' activeIndex={activeTab} onTabChange={(e) => setActiveTab(e.index)}>
                        <TabPanel header="Mention" className='flex flex-col items-center'>
                            <DataTable value={mentions} paginator rows={7} dataKey="id" sortField="mention" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="icon" header="Icon" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="mention" header="Mention" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Niveau" className='flex flex-col items-center'>
                            <DataTable value={niveaux} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="nom" header="Nom" sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column field="frais" header="Frais d'inscription" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Parcours" className='flex flex-col items-center'>
                            <DataTable value={parcours} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="nom" header="Nom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'mention', mentionOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="niveau" header="Niveau" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'niveau', niveauOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Semestre" className='flex flex-col items-center'>
                            <DataTable value={semestres} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()} emptyMessage="Aucune donnée trouvée."
                                className='w-full'>
                                <Column field="nom" header="Nom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Unité d'Enseignement" className='flex flex-col items-center'>
                            <DataTable value={ues} paginator rows={7} dataKey="id" sortField="numero" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="numero" header="N°" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'mention', mentionOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="parcours" header="Parcours" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'parcours', parcoursOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="semestre" header="Semestre" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'semestre', semestreOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Elément Constitutif" className='flex flex-col items-center'>
                            <DataTable value={ecs} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="nom" header="Nom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="UE" header="Unité d'enseignement" body={(rowData) =>
                                    relationBodyTemplate(rowData, 'UE', ueOptions)} sortable style={{ minWidth: '5rem' }} />
                                <Column field="professeur" header="Professeur" sortable style={{ minWidth: '5rem' }} />
                                <Column field="code" header="Code" sortable style={{ minWidth: '5rem' }} />
                                <Column field="credit" header="Crédit" sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={(rowData) => booleanBodyTemplate(rowData, 'statut')}
                                    style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Année Universitaire" className='flex flex-col items-center'>
                            <DataTable value={annees} paginator rows={7} dataKey="id" sortField="year" sortOrder={1}
                                globalFilter={globalFilterValue} header={renderHeader()}
                                emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="year" header="Année scolaire" style={{ minwidth: '5rem' }} />
                                <Column field="actuel" header="Actuel" body={(rowData) => booleanBodyTemplate(rowData, 'actuel')}
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
                                        severity='danger'
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