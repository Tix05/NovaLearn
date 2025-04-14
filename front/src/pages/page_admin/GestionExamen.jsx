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
import { TabView, TabPanel } from 'primereact/tabview';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Trash2, Eye, Calendar, Send, Download, Printer } from 'lucide-react';

export default function GestionExamen() {
    // Examens des étudiants (onglet 1)
    const [studentExams, setStudentExams] = useState([
        {
            id: 1,
            matricule: 'ET001',
            nomPrenom: 'Jean Dupont',
            mention: 'Informatique',
            niveau: 'Licence 2',
            elementConstitutif: 'Programmation Web',
            dateSoumission: '2023-05-15 14:30',
            anneeUniversitaire: '2022-2023'
        },
        {
            id: 2,
            matricule: 'ET002',
            nomPrenom: 'Marie Martin',
            mention: 'Mathématiques',
            niveau: 'Master 1',
            elementConstitutif: 'Analyse complexe',
            dateSoumission: '2023-06-20 10:15',
            anneeUniversitaire: '2023-2024'
        }
    ]);

    // Sujets d'examen des professeurs (onglet 2)
    const [teacherExams, setTeacherExams] = useState([
        {
            id: 1,
            nomPrenom: 'Prof. Ahmed Khan',
            mention: 'Informatique',
            niveau: 'Licence 3',
            elementConstitutif: 'Base de données',
            dateEnvoi: '2023-09-10 09:45',
            anneeUniversitaire: '2023-2024',
            statut: 'en_attente'
        },
        {
            id: 2,
            nomPrenom: 'Prof. Sophie Leroy',
            mention: 'Physique',
            niveau: 'Licence 1',
            elementConstitutif: 'Mécanique du point',
            dateEnvoi: '2023-09-15 11:20',
            anneeUniversitaire: '2023-2024',
            statut: 'publie'
        }
    ]);

    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [elementConstitutifFilter, setElementConstitutifFilter] = useState('Tous');
    const [anneeUniversitaireFilter, setAnneeUniversitaireFilter] = useState('Tous');
    const [deleteDialogVisible, setDeleteDialogVisible] = useState(false);
    const [publishDialogVisible, setPublishDialogVisible] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [activeTabIndex, setActiveTabIndex] = useState(0);
    const toast = useRef(null);

    // Options de filtre
    const allMentions = Array.from(new Set([
        ...studentExams.map(item => item.mention),
        ...teacherExams.map(item => item.mention)
    ]));
    const mentions = [{ label: 'Mention', value: 'Tous' }, ...allMentions.map(m => ({ label: m, value: m }))];

    const allNiveaux = Array.from(new Set([
        ...studentExams.map(item => item.niveau),
        ...teacherExams.map(item => item.niveau)
    ]));
    const niveaux = [{ label: 'Niveau', value: 'Tous' }, ...allNiveaux.map(n => ({ label: n, value: n }))];

    const allElements = Array.from(new Set([
        ...studentExams.map(item => item.elementConstitutif),
        ...teacherExams.map(item => item.elementConstitutif)
    ]));
    const elementsConstitutifs = [{ label: 'Élément constitutif', value: 'Tous' }, ...allElements.map(c => ({ label: c, value: c }))];

    const anneesUniversitaires = [
        { label: 'Année universitaire', value: 'Tous' },
        { label: '2022-2023', value: '2022-2023' },
        { label: '2023-2024', value: '2023-2024' },
        { label: '2024-2025', value: '2024-2025' }
    ];

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    // Actions pour les examens étudiants
    const confirmDeleteStudentExam = (item) => {
        setSelectedItem({ ...item, type: 'student' });
        setDeleteDialogVisible(true);
    };

    const deleteStudentExam = () => {
        setStudentExams(studentExams.filter(item => item.id !== selectedItem.id));
        setDeleteDialogVisible(false);
        showToast('success', `L'examen de ${selectedItem.nomPrenom} a été supprimé`);
    };

    // Actions pour les sujets professeurs
    const confirmDeleteTeacherExam = (item) => {
        setSelectedItem({ ...item, type: 'teacher' });
        setDeleteDialogVisible(true);
    };

    const confirmPublishExam = (item) => {
        setSelectedItem(item);
        setPublishDialogVisible(true);
    };

    const deleteTeacherExam = () => {
        setTeacherExams(teacherExams.filter(item => item.id !== selectedItem.id));
        setDeleteDialogVisible(false);
        showToast('success', `Le sujet de ${selectedItem.nomPrenom} a été supprimé`);
    };

    const publishExam = () => {
        setTeacherExams(teacherExams.map(item =>
            item.id === selectedItem.id ? { ...item, statut: 'publie' } : item
        ));
        setPublishDialogVisible(false);
        showToast('success', `Le sujet de ${selectedItem.elementConstitutif} a été publié`);
    };

    const showToast = (severity, detail) => {
        toast.current.show({
            severity,
            summary: 'Succès',
            detail,
            life: 3000
        });
    };

    const handleDownload = (filename) => {
        showToast('info', `Téléchargement de ${filename} en cours`);
        // Implémentation réelle du téléchargement
    };

    const handlePrint = (item) => {
        showToast('info', `Impression de ${item.elementConstitutif}`);
    };

    const handleView = (item) => {
        showToast('info', `Visualisation de ${item.elementConstitutif}`);
    };

    // Filtrage des données
    const filteredStudentExams = studentExams.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (elementConstitutifFilter === 'Tous' || item.elementConstitutif === elementConstitutifFilter) &&
        (anneeUniversitaireFilter === 'Tous' || item.anneeUniversitaire === anneeUniversitaireFilter) &&
        (item.nomPrenom.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.matricule.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.mention.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.niveau.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.elementConstitutif.toLowerCase().includes(globalFilterValue.toLowerCase()))
    );

    const filteredTeacherExams = teacherExams.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (elementConstitutifFilter === 'Tous' || item.elementConstitutif === elementConstitutifFilter) &&
        (anneeUniversitaireFilter === 'Tous' || item.anneeUniversitaire === anneeUniversitaireFilter) &&
        (item.nomPrenom.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.mention.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.niveau.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.elementConstitutif.toLowerCase().includes(globalFilterValue.toLowerCase()))
    );

    // Templates d'actions
    const studentExamActions = (rowData) => (
        <div className='flex items-center gap-x-2'>
            <Button
                icon={<Download size={18} />}
                rounded
                severity="secondary"
                onClick={() => handleDownload(rowData.elementConstitutif)}
                tooltip="Télécharger"
                tooltipOptions={{ position: 'top' }}
            />
            <Button
                icon={<Printer size={18} />}
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
                onClick={() => confirmDeleteStudentExam(rowData)}
                tooltip="Supprimer"
                tooltipOptions={{ position: 'top' }}
            />
        </div>
    );

    const teacherExamActions = (rowData) => (
        <div className='flex items-center gap-x-2'>
            <Button
                icon={<Eye size={18} />}
                rounded
                severity="info"
                onClick={() => handleView(rowData)}
                tooltip="Voir"
                tooltipOptions={{ position: 'top' }}
            />
            <Button
                icon={<Calendar size={18} />}
                rounded
                severity="success"
                onClick={() => confirmPublishExam(rowData)}
                tooltip="Publier dans agenda"
                tooltipOptions={{ position: 'top' }}
                disabled={rowData.statut === 'publie'}
            />
            <Button
                icon={<Trash2 size={18} />}
                rounded
                severity="danger"
                onClick={() => confirmDeleteTeacherExam(rowData)}
                tooltip="Supprimer"
                tooltipOptions={{ position: 'top' }}
            />
        </div>
    );

    const statusTemplate = (rowData) => (
        <span className={`p-tag ${rowData.statut === 'publie' ? 'p-tag-success' : 'p-tag-warning'}`}>
            {rowData.statut === 'publie' ? 'Publié' : 'En attente'}
        </span>
    );

    const renderHeader = () => (
        <div className="flex flex-col space-y-4">
            <div className='flex items-center justify-between p-5'>
                <h1 className='text-3xl font-semibold'>Gestion des examens</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={globalFilterValue}
                        onChange={onGlobalFilterChange}
                        placeholder="Rechercher..."
                        className='custom-input'
                    />
                </IconField>
            </div>
            <div className='grid md:grid-cols-2 grid-cols-1 justify-start gap-3 items-center'>
                <div className="flex flex-wrap items-center justify-center gap-3 bibliotheque-dropdown w-full">
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

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="top-right" />
            <div className="relative custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <div className="p-4">
                    <TabView activeIndex={activeTabIndex} onTabChange={(e) => setActiveTabIndex(e.index)}>
                        {/* Onglet 1: Examens des étudiants */}
                        <TabPanel header="Examens des étudiants">
                            <DataTable
                                value={filteredStudentExams}
                                paginator
                                rows={10}
                                dataKey="id"
                                sortField="nomPrenom"
                                sortOrder={1}
                                header={renderHeader()}
                                emptyMessage="Aucun examen étudiant trouvé."
                                scrollable
                                scrollHeight="flex"
                            >
                                <Column field="matricule" header="Matricule" sortable style={{ minWidth: '10rem' }} />
                                <Column field="nomPrenom" header="Nom et Prénom" sortable style={{ minWidth: '12rem' }} />
                                <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                                <Column field="niveau" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                                <Column field="elementConstitutif" header="Élément constitutif" sortable style={{ minWidth: '12rem' }} />
                                <Column field="dateSoumission" header="Date de soumission" sortable style={{ minWidth: '12rem' }} />
                                <Column field="anneeUniversitaire" header="Année universitaire" sortable style={{ minWidth: '12rem' }} />
                                <Column
                                    body={studentExamActions}
                                    header="Actions"
                                    style={{ minWidth: '12rem' }}
                                    exportable={false}
                                />
                            </DataTable>
                        </TabPanel>

                        {/* Onglet 2: Sujets des professeurs */}
                        <TabPanel header="Sujets des professeurs">
                            <DataTable
                                value={filteredTeacherExams}
                                paginator
                                rows={10}
                                dataKey="id"
                                sortField="nomPrenom"
                                sortOrder={1}
                                header={renderHeader()}
                                emptyMessage="Aucun sujet professeur trouvé."
                                scrollable
                                scrollHeight="flex"
                            >
                                <Column field="nomPrenom" header="Nom du professeur" sortable style={{ minWidth: '12rem' }} />
                                <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                                <Column field="niveau" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                                <Column field="elementConstitutif" header="Élément constitutif" sortable style={{ minWidth: '12rem' }} />
                                <Column field="dateEnvoi" header="Date d'envoi" sortable style={{ minWidth: '12rem' }} />
                                <Column field="anneeUniversitaire" header="Année universitaire" sortable style={{ minWidth: '12rem' }} />
                                <Column
                                    field="statut"
                                    header="Statut"
                                    body={statusTemplate}
                                    style={{ minWidth: '10rem' }}
                                />
                                <Column
                                    body={teacherExamActions}
                                    header="Actions"
                                    style={{ minWidth: '12rem' }}
                                    exportable={false}
                                />
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>

                {/* Dialogue de suppression */}
                <Dialog
                    visible={deleteDialogVisible}
                    style={{ width: '450px' }}
                    header="Confirmer la suppression"
                    modal
                    footer={
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
                                onClick={selectedItem?.type === 'student' ? deleteStudentExam : deleteTeacherExam}
                                severity="danger"
                                autoFocus
                            />
                        </>
                    }
                    onHide={() => setDeleteDialogVisible(false)}
                >
                    <div className="flex align-items-center justify-content-center">
                        <i className="pi pi-exclamation-triangle mr-3" style={{ fontSize: '2rem', color: '#f8bb86' }} />
                        {selectedItem && (
                            <span>
                                Êtes-vous sûr de vouloir supprimer {selectedItem.type === 'student'
                                    ? `l'examen de ${selectedItem.nomPrenom}`
                                    : `le sujet de ${selectedItem.nomPrenom}`} ?
                                <br />
                                Cette action est irréversible.
                            </span>
                        )}
                    </div>
                </Dialog>

                {/* Dialogue de publication */}
                <Dialog
                    visible={publishDialogVisible}
                    style={{ width: '450px' }}
                    header="Confirmer la publication"
                    modal
                    footer={
                        <>
                            <Button
                                label="Annuler"
                                icon="pi pi-times"
                                onClick={() => setPublishDialogVisible(false)}
                                className="p-button-text"
                            />
                            <Button
                                label="Publier"
                                icon="pi pi-check"
                                onClick={publishExam}
                                severity="success"
                                autoFocus
                            />
                        </>
                    }
                    onHide={() => setPublishDialogVisible(false)}
                >
                    <div className="flex align-items-center justify-content-center">
                        <i className="pi pi-info-circle mr-3" style={{ fontSize: '2rem', color: '#42A5F5' }} />
                        {selectedItem && (
                            <span>
                                Êtes-vous sûr de vouloir publier le sujet <b>{selectedItem.elementConstitutif}</b> dans l'agenda ?
                            </span>
                        )}
                    </div>
                </Dialog>
            </div>
        </LayoutAdmin>
    );
}