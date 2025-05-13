import React, { useState, useEffect, useRef } from 'react';
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
import { Calendar } from 'primereact/calendar';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Trash2, Eye, Send } from 'lucide-react';
import { getTeacherExams, deleteTeacherExam, publishTeacherExam, previewTeacherExam } from '../../Services/gestionExamenService';
import { getCurrentAdmin } from '../../Services/adminAuthService';

export default function GestionExamen() {
    const [teacherExams, setTeacherExams] = useState([]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [elementConstitutifFilter, setElementConstitutifFilter] = useState('Tous');
    const [anneeUniversitaireFilter, setAnneeUniversitaireFilter] = useState('Tous');
    const [deleteDialogVisible, setDeleteDialogVisible] = useState(false);
    const [publishDialogVisible, setPublishDialogVisible] = useState(false);
    const [previewDialogVisible, setPreviewDialogVisible] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [previewData, setPreviewData] = useState(null);
    const [activeTabIndex, setActiveTabIndex] = useState(1);
    const [dateDebut, setDateDebut] = useState(null);
    const [dateFin, setDateFin] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const toast = useRef(null);

    useEffect(() => {
        const checkAuth = async () => {
            const admin = getCurrentAdmin();
            if (!admin || !admin.roles?.includes('ROLE_ADMIN')) {
                toast.current.show({
                    severity: 'error',
                    summary: 'Accès refusé',
                    detail: 'Vous devez être administrateur pour accéder à cette page',
                    life: 3000,
                });
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
                return;
            }

            setIsLoading(true);
            try {
                const exams = await getTeacherExams();
                // Filtrer pour exclure les fichiers temporaires
                const filteredExams = exams.filter(exam =>
                    exam.statut === 'en_attente' &&
                    exam.fichier &&
                    !exam.fichier.startsWith('temp_') &&
                    !exam.fichier.includes('Temporary: DoNotDisplay')
                );
                setTeacherExams(filteredExams);
            } catch (error) {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message || 'Erreur lors de la récupération des examens',
                    life: 3000,
                });
            } finally {
                setIsLoading(false);
            }
        };

        checkAuth();
    }, []);

    const allMentions = Array.from(new Set(teacherExams.map(item => item.mention)));
    const mentions = [{ label: 'Mention', value: 'Tous' }, ...allMentions.map(m => ({ label: m, value: m }))];

    const allNiveaux = Array.from(new Set(teacherExams.map(item => item.niveau)));
    const niveaux = [{ label: 'Niveau', value: 'Tous' }, ...allNiveaux.map(n => ({ label: n, value: n }))];

    const allElements = Array.from(new Set(teacherExams.map(item => item.elementConstitutif)));
    const elementsConstitutifs = [{ label: 'Élément constitutif', value: 'Tous' }, ...allElements.map(c => ({ label: c, value: c }))];

    const anneesUniversitaires = [
        { label: 'Année universitaire', value: 'Tous' },
        { label: '2022-2023', value: '2022-2023' },
        { label: '2023-2024', value: '2023-2024' },
        { label: '2024-2025', value: '2024-2025' },
    ];

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const confirmDeleteTeacherExam = (item) => {
        setSelectedItem({ ...item, type: 'teacher' });
        setDeleteDialogVisible(true);
    };

    const deleteTeacherExamAction = async () => {
        try {
            await deleteTeacherExam(selectedItem.id);
            setTeacherExams(teacherExams.filter(item => item.id !== selectedItem.id));
            setDeleteDialogVisible(false);
            showToast('success', `Le sujet de ${selectedItem.nomPrenom} a été supprimé`);
        } catch (error) {
            showToast('error', error.message || 'Erreur lors de la suppression');
        }
    };

    const confirmPublishExam = (item) => {
        setSelectedItem(item);
        setDateDebut(null);
        setDateFin(null);
        setPublishDialogVisible(true);
    };

    const handlePreviewExam = async () => {
        if (!dateDebut || !dateFin) {
            showToast('error', 'Veuillez sélectionner les dates de début et de fin');
            return;
        }
        if (dateDebut >= dateFin) {
            showToast('error', 'La date de fin doit être postérieure à la date de début');
            return;
        }

        try {
            const response = await previewTeacherExam(selectedItem.id, {
                date_debut: dateDebut.toISOString(),
                date_fin: dateFin.toISOString(),
            });
            setPreviewData(response.preview);
            setPublishDialogVisible(false);
            setPreviewDialogVisible(true);
        } catch (error) {
            showToast('error', error.message || 'Erreur lors de la prévisualisation');
        }
    };

    const publishExam = async () => {
        try {
            await publishTeacherExam(selectedItem.id, {
                date_debut: dateDebut.toISOString(),
                date_fin: dateFin.toISOString(),
            });
            setTeacherExams(teacherExams.filter(item => item.id !== selectedItem.id));
            setPreviewDialogVisible(false);
            showToast('success', `Le sujet de ${selectedItem.elementConstitutif} a été publié`);
        } catch (error) {
            showToast('error', error.message || 'Erreur lors de la publication');
        }
    };

    const showToast = (severity, detail) => {
        toast.current.show({
            severity,
            summary: severity === 'success' ? 'Succès' : 'Erreur',
            detail,
            life: 3000,
        });
    };

    const handleView = (item) => {
        if (item.fichier) {
            window.open(`http://localhost:8000${item.fichier}`, '_blank');
        } else {
            showToast('error', 'Aucun fichier disponible');
        }
    };

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
                icon={<Send size={18} />}
                rounded
                severity="success"
                onClick={() => confirmPublishExam(rowData)}
                tooltip="Publier dans agenda"
                tooltipOptions={{ position: 'top' }}
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
        <span className="p-tag p-tag-warning">
            En attente
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
                onClick={deleteTeacherExamAction}
                severity="danger"
                autoFocus
            />
        </>
    );

    const publishDialogFooter = (
        <>
            <Button
                label="Annuler"
                icon="pi pi-times"
                onClick={() => setPublishDialogVisible(false)}
                className="p-button-text"
            />
            <Button
                label="Prévisualiser"
                icon="pi pi-eye"
                onClick={handlePreviewExam}
                severity="info"
                autoFocus
            />
        </>
    );

    const previewDialogFooter = (
        <>
            <Button
                label="Annuler"
                icon="pi pi-times"
                onClick={() => setPreviewDialogVisible(false)}
                className="p-button-text"
            />
            <Button
                label="Confirmer la publication"
                icon="pi pi-check"
                onClick={publishExam}
                severity="success"
                autoFocus
            />
        </>
    );

    return (
        <LayoutAdmin>
            <Toast ref={toast} position="top-right" />
            <div className="relative custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <div>
                    <TabView activeIndex={activeTabIndex} onTabChange={(e) => setActiveTabIndex(e.index)} className='custom-tabview'>
                        <TabPanel header="Examens des étudiants" disabled>
                            <DataTable
                                value={[]}
                                emptyMessage="Non disponible"
                            />
                        </TabPanel>
                        <TabPanel header="Sujets des professeurs">
                            {isLoading ? (
                                <div className="flex justify-center items-center h-64">
                                    <i className="pi pi-spin pi-spinner" style={{ fontSize: '2rem' }}></i>
                                </div>
                            ) : (
                                <DataTable
                                    value={filteredTeacherExams}
                                    paginator
                                    rows={10}
                                    dataKey="id"
                                    sortField="nomPrenom"
                                    sortOrder={1}
                                    header={renderHeader()}
                                    emptyMessage="Aucun sujet professeur en attente."
                                    scrollable
                                    scrollHeight="flex"
                                >
                                    <Column field="nomPrenom" header="Nom du professeur" sortable style={{ minWidth: '12rem' }} />
                                    <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '8rem' }} />
                                    <Column field="elementConstitutif" header="Élément constitutif" sortable style={{ minWidth: '12rem' }} />
                                    <Column field="anneeUniversitaire" header="Année universitaire" sortable style={{ minWidth: '10rem' }} />
                                    <Column body={statusTemplate} header="Statut" style={{ minWidth: '8rem' }} />
                                    <Column body={teacherExamActions} header="Actions" style={{ minWidth: '10rem' }} />
                                </DataTable>
                            )}
                        </TabPanel>
                    </TabView>
                </div>

                <Dialog
                    header="Confirmer la suppression"
                    visible={deleteDialogVisible}
                    onHide={() => setDeleteDialogVisible(false)}
                    footer={deleteDialogFooter}
                    style={{ width: '30rem' }}
                >
                    <div className="flex items-center gap-3">
                        <i className="pi pi-exclamation-triangle" style={{ fontSize: '2rem', color: 'red' }} />
                        <span>
                            Êtes-vous sûr de vouloir supprimer le sujet de <b>{selectedItem?.nomPrenom}</b> ?
                        </span>
                    </div>
                </Dialog>

                <Dialog
                    header="Publier l'examen"
                    visible={publishDialogVisible}
                    onHide={() => setPublishDialogVisible(false)}
                    footer={publishDialogFooter}
                    style={{ width: '40rem' }}
                >
                    <div className="p-fluid">
                        <div className="field mb-4">
                            <label htmlFor="dateDebut">Date de début</label>
                            <Calendar
                                id="dateDebut"
                                value={dateDebut}
                                onChange={(e) => setDateDebut(e.value)}
                                showTime
                                hourFormat="24"
                                dateFormat="dd/mm/yy"
                                placeholder="Sélectionner la date de début"
                                required
                            />
                        </div>
                        <div className="field">
                            <label htmlFor="dateFin">Date de fin</label>
                            <Calendar
                                id="dateFin"
                                value={dateFin}
                                onChange={(e) => setDateFin(e.value)}
                                showTime
                                hourFormat="24"
                                dateFormat="dd/mm/yy"
                                placeholder="Sélectionner la date de fin"
                                required
                            />
                        </div>
                    </div>
                </Dialog>

                <Dialog
                    header="Prévisualisation de l'examen"
                    visible={previewDialogVisible}
                    onHide={() => setPreviewDialogVisible(false)}
                    footer={previewDialogFooter}
                    style={{ width: '50rem' }}
                >
                    {previewData ? (
                        <div className="space-y-4">
                            <p><strong>Élément constitutif :</strong> {selectedItem?.elementConstitutif}</p>
                            <p><strong>Professeur :</strong> {selectedItem?.nomPrenom}</p>
                            <p><strong>Date de début :</strong> {dateDebut?.toLocaleString()}</p>
                            <p><strong>Date de fin :</strong> {dateFin?.toLocaleString()}</p>
                            <p><strong>Contenu :</strong></p>
                            <embed
                                src={`http://localhost:8000${selectedItem?.fichier}`}
                                type="application/pdf"
                                width="100%"
                                height="400px"
                                className="border rounded"
                            />
                        </div>
                    ) : (
                        <p>Chargement de la prévisualisation...</p>
                    )}
                </Dialog>
            </div>
        </LayoutAdmin>
    );
}