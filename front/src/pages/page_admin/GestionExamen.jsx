import React, { useState, useRef } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { Toast } from 'primereact/toast';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Trash2 } from 'lucide-react';

export default function GestionExamen() {
    const [data, setData] = useState([
        {
            id: 1,
            profil: 'Enseignant',
            nomPrenom: 'John Doe',
            mention: 'Mathématiques',
            niveau: 'Licence 1',
            elementConstitutif: 'Algèbre linéaire',
            dateEnvoi: '2023-05-15 14:30',
            anneeUniversitaire: '2022-2023'
        },
        {
            id: 2,
            profil: 'Enseignant',
            nomPrenom: 'Jane Smith',
            mention: 'Physique',
            niveau: 'Master 2',
            elementConstitutif: 'Mécanique quantique',
            dateEnvoi: '2023-06-20 10:15',
            anneeUniversitaire: '2023-2024'
        },
        {
            id: 3,
            profil: 'Enseignant',
            nomPrenom: 'Robert Johnson',
            mention: 'Informatique',
            niveau: 'Licence 3',
            elementConstitutif: 'Algorithmique avancée',
            dateEnvoi: '2023-07-10 09:45',
            anneeUniversitaire: '2023-2024'
        },
    ]);

    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [elementConstitutifFilter, setElementConstitutifFilter] = useState('Tous');
    const [anneeUniversitaireFilter, setAnneeUniversitaireFilter] = useState('Tous');
    const [deleteDialogVisible, setDeleteDialogVisible] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const toast = useRef(null);

    const mentions = [{ label: 'Mention', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.mention))).map(m => ({ label: m, value: m }))];
    const niveaux = [{ label: 'Niveau', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.niveau))).map(n => ({ label: n, value: n }))];
    const elementsConstitutifs = [{ label: 'Élément constitutif', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.elementConstitutif))).map(c => ({ label: c, value: c }))];
    const anneesUniversitaires = [
        { label: 'Année universitaire', value: 'Tous' },
        { label: '2022-2023', value: '2022-2023' },
        { label: '2023-2024', value: '2023-2024' },
        { label: '2024-2025', value: '2024-2025' }
    ];

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const confirmDelete = (item) => {
        setSelectedItem(item);
        setDeleteDialogVisible(true);
    };

    const deleteItem = () => {
        const newData = data.filter(item => item.id !== selectedItem.id);
        setData(newData);
        setDeleteDialogVisible(false);

        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: `L'examen "${selectedItem.elementConstitutif}" a été supprimé avec succès`,
            life: 3000
        });
    };

    const handleDownload = (filename) => {
        const link = document.createElement('a');
        link.href = `/path/to/examens/${filename}`;
        link.download = filename;
        link.click();

        toast.current.show({
            severity: 'info',
            summary: 'Téléchargement',
            detail: 'Le téléchargement a commencé',
            life: 3000
        });
    };

    const handlePrint = (item) => {
        toast.current.show({
            severity: 'info',
            summary: 'Impression',
            detail: `Impression de l'examen ${item.elementConstitutif}`,
            life: 3000
        });
    };

    const filteredData = data.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (elementConstitutifFilter === 'Tous' || item.elementConstitutif === elementConstitutifFilter) &&
        (anneeUniversitaireFilter === 'Tous' || item.anneeUniversitaire === anneeUniversitaireFilter) &&
        (item.nomPrenom.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.mention.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.niveau.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.elementConstitutif.toLowerCase().includes(globalFilterValue.toLowerCase()))
    );

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex items-center gap-x-2'>
                <Button
                    icon="pi pi-download"
                    rounded
                    severity="secondary"
                    onClick={() => handleDownload(rowData.elementConstitutif)}
                    tooltip="Télécharger"
                    tooltipOptions={{ position: 'top' }}
                />
                <Button
                    icon="pi pi-print"
                    rounded
                    severity="info"
                    onClick={() => handlePrint(rowData)}
                    tooltip="Imprimer"
                    tooltipOptions={{ position: 'top' }}
                />
                <Button
                    icon={<Trash2 size={18} />}
                    rounded
                    severity="danger"
                    onClick={() => confirmDelete(rowData)}
                    tooltip="Supprimer"
                    tooltipOptions={{ position: 'top' }}
                />
            </div>
        );
    };

    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className='text-3xl p-5 font-semibold'>Gestion des examens</h1>
                <div className='grid md:grid-cols-2 grid-cols-1 justify-start gap-3 items-center'>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText
                            value={globalFilterValue}
                            onChange={onGlobalFilterChange}
                            placeholder="Rechercher..."
                            className='custom-input'
                        />
                    </IconField>

                    <div className="flex flex-wrap items-center justify-center gap-3 bibliotheque-dropdown ">
                        <Dropdown
                            value={mentionFilter}
                            onChange={(e) => setMentionFilter(e.value)}
                            options={mentions}
                            optionLabel="label"
                            placeholder="Mention"
                            className="rounded font-poppins text-sm bg-white"
                        />

                        <Dropdown
                            value={niveauFilter}
                            onChange={(e) => setNiveauFilter(e.value)}
                            options={niveaux}
                            optionLabel="label"
                            placeholder="Niveau"
                            className="rounded font-poppins text-sm bg-white"
                        />

                        <Dropdown
                            value={elementConstitutifFilter}
                            onChange={(e) => setElementConstitutifFilter(e.value)}
                            options={elementsConstitutifs}
                            optionLabel="label"
                            placeholder="Élément constitutif"
                            className="rounded font-poppins text-sm bg-white"
                        />

                        <Dropdown
                            value={anneeUniversitaireFilter}
                            onChange={(e) => setAnneeUniversitaireFilter(e.value)}
                            options={anneesUniversitaires}
                            optionLabel="label"
                            placeholder="Année universitaire"
                            className="rounded font-poppins text-sm bg-white"
                        />
                    </div>
                </div>
            </div>
        );
    };

    const deleteDialogFooter = (
        <>
            <Button
                label="Non"
                icon="pi pi-times"
                onClick={() => setDeleteDialogVisible(false)}
                className="p-button-text"
            />
            <Button
                label="Oui"
                icon="pi pi-check"
                onClick={deleteItem}
                severity="danger"
                autoFocus
            />
        </>
    );

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="top-right" />
            <div className="relative custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <div className="p-4">
                    <DataTable
                        value={filteredData}
                        paginator
                        rows={10}
                        dataKey="id"
                        sortField="nomPrenom"
                        sortOrder={1}
                        header={renderHeader()}
                        emptyMessage="Aucun examen trouvé."
                        scrollable
                        scrollHeight="flex"
                    >
                        <Column field="profil" header="Profil" sortable style={{ minWidth: '10rem' }} />
                        <Column field="nomPrenom" header="Nom et Prénom" sortable style={{ minWidth: '12rem' }} />
                        <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                        <Column field="niveau" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                        <Column field="elementConstitutif" header="Élément constitutif" sortable style={{ minWidth: '12rem' }} />
                        <Column field="dateEnvoi" header="Date et heure d'envoi" sortable style={{ minWidth: '12rem' }} />
                        <Column field="anneeUniversitaire" header="Année universitaire" sortable style={{ minWidth: '12rem' }} />
                        <Column
                            body={actionBodyTemplate}
                            header="Actions"
                            style={{ minWidth: '12rem' }}
                            exportable={false}
                        />
                    </DataTable>
                </div>

                <Dialog
                    visible={deleteDialogVisible}
                    style={{ width: '450px' }}
                    header="Confirmer la suppression"
                    modal
                    footer={deleteDialogFooter}
                    onHide={() => setDeleteDialogVisible(false)}
                >
                    <div className="flex align-items-center justify-content-center">
                        <i className="pi pi-exclamation-triangle mr-3" style={{ fontSize: '2rem', color: '#f8bb86' }} />
                        {selectedItem && (
                            <span>
                                Êtes-vous sûr de vouloir supprimer l'examen de <b>{selectedItem.elementConstitutif}</b> ?
                                <br />
                                Cette action est irréversible.
                            </span>
                        )}
                    </div>
                </Dialog>
            </div>
        </LayoutAdmin>
    );
}