import React, { useState, useRef } from 'react';
import { TabView, TabPanel } from 'primereact/tabview';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { InputText } from 'primereact/inputtext';
import { InputTextarea } from 'primereact/inputtextarea';
import { Calendar } from 'primereact/calendar';
import { motion, AnimatePresence } from 'framer-motion';
import { Toast } from 'primereact/toast';
import { confirmDialog } from 'primereact/confirmdialog';

export default function AgendaAdmin() {
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const [showCreateDialog, setShowCreateDialog] = useState(false);
    const [agendaForm, setAgendaForm] = useState({
        titre: '',
        date: null,
        description: '',
        lien: '',
        type: 'cours'
    });

    const [coursData, setCoursData] = useState([
        { id: 1, titre: 'Cours de Mathématiques', date: '2023-10-24', description: '../../../public/images/icon-esum.png', professeur: 'Prof. Dupont' },
        { id: 2, titre: 'Cours de Physique', date: '2023-10-25', description: 'Lorem ipsum dolor sit amet...', professeur: 'Prof. Martin' },
    ]);

    const [examensData, setExamensData] = useState([
        { id: 1, titre: 'Examen de Mathématiques', date: '2023-11-10', description: 'Examen final', professeur: 'Prof. Dupont' },
    ]);

    const [evenementsData, setEvenementsData] = useState([
        { id: 1, titre: 'Conférence sur l\'IA', date: '2023-10-30', description: 'Conférence avec un expert en IA', organisateur: 'Dr. Smith' },
    ]);

    const toast = useRef(null);
    const periodeOptions = [
        { label: 'Tout', value: 'Tout' },
        { label: 'Semaine', value: 'Semaine' },
        { label: 'Mois', value: 'Mois' },
        { label: 'Trimestre', value: 'Trimestre' },
        { label: 'Semestre', value: 'Semestre' },
    ];

    const typeOptions = [
        { label: 'Cours', value: 'cours' },
        { label: 'Examen', value: 'examen' },
        { label: 'Évènement', value: 'evenement' }
    ];

    const filterData = (data) => {
        const today = new Date();
        const selectedDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine':
                selectedDate.setDate(today.getDate() + 7);
                break;
            case 'Mois':
                selectedDate.setMonth(today.getMonth() + 1);
                break;
            case 'Trimestre':
                selectedDate.setMonth(today.getMonth() + 3);
                break;
            case 'Semestre':
                selectedDate.setMonth(today.getMonth() + 6);
                break;
            default:
                return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            return itemDate <= selectedDate && itemDate >= today;
        });
    };

    const handleFormChange = (e) => {
        const { name, value } = e.target;
        setAgendaForm(prev => ({ ...prev, [name]: value }));
    };

    const handleDateChange = (e) => {
        setAgendaForm(prev => ({ ...prev, date: e.value }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const newItem = {
            id: Math.max(...[...coursData, ...examensData, ...evenementsData].map(i => i.id)) + 1,
            titre: agendaForm.titre,
            date: agendaForm.date.toISOString().split('T')[0],
            description: agendaForm.description,
            ...(agendaForm.lien && { lien: agendaForm.lien }),
            ...(agendaForm.type === 'cours' && { professeur: 'Nouveau Professeur' }),
            ...(agendaForm.type === 'evenement' && { organisateur: 'Nouvel Organisateur' })
        };

        switch (agendaForm.type) {
            case 'cours':
                setCoursData([...coursData, newItem]);
                break;
            case 'examen':
                setExamensData([...examensData, newItem]);
                break;
            case 'evenement':
                setEvenementsData([...evenementsData, newItem]);
                break;
        }

        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: 'Élément ajouté à l\'agenda',
            life: 3000
        });

        setShowCreateDialog(false);
        setAgendaForm({
            titre: '',
            date: null,
            description: '',
            lien: '',
            type: 'cours'
        });
    };

    const confirmDelete = (type, id) => {
        confirmDialog({
            message: 'Êtes-vous sûr de vouloir supprimer cet élément ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: () => deleteItem(type, id),
            acceptClassName: 'p-button-danger'
        });
    };

    const deleteItem = (type, id) => {
        switch (type) {
            case 'cours':
                setCoursData(coursData.filter(item => item.id !== id));
                break;
            case 'examen':
                setExamensData(examensData.filter(item => item.id !== id));
                break;
            case 'evenement':
                setEvenementsData(evenementsData.filter(item => item.id !== id));
                break;
        }

        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: 'Élément supprimé de l\'agenda',
            life: 3000
        });
    };

    const renderItem = (item, type) => (
        <div key={item.id} className='flex border-2 border-gray-400 mb-4 w-[100%] md:w-[80%] rounded-sm relative'>
            {/* Bouton de suppression */}
            <button
                className="absolute top-2 right-2 text-red-500 hover:text-red-700"
                onClick={() => confirmDelete(type, item.id)}
            >
                <i className="pi pi-trash"></i>
            </button>

            <div className='flex flex-col text-4xl items-center p-5 flex-shrink-0'>
                <h1 className='font-bold'>{new Date(item.date).getDate()}</h1>
                <h1 className='font-normal'>{new Date(item.date).toLocaleString('default', { month: 'long' })}</h1>
            </div>
            <Divider layout="vertical" />
            <div className='flex flex-col p-3 space-y-5 overflow-hidden w-full items-center justify-center'>
                <h1 className='text-2xl font-semibold text-blue-600 overflow-wrap break-word'>
                    {item.titre}
                </h1>

                {typeof item.description === 'string' && item.description.endsWith('.png' || '.jpg') ? (
                    <img src={item.description} alt={item.titre} className="w-32 h-32 object-cover rounded-lg" />
                ) : (
                    <p className='overflow-wrap break-word'>{item.description}</p>
                )}

                <h1 className='font-semibold text-xl overflow-wrap break-word'>
                    {item.professeur || item.organisateur}
                </h1>
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
                onClick={() => setShowCreateDialog(true)}
            />
        </div>
    );

    return (
        <LayoutAdmin>
            <Toast ref={toast} />
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <TabView className='custom-tabview'>
                    <TabPanel header="Cours" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        {filterData(coursData).map(item => renderItem(item, 'cours'))}
                    </TabPanel>
                    <TabPanel header="Examens" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        {filterData(examensData).map(item => renderItem(item, 'examen'))}
                    </TabPanel>
                    <TabPanel header="Evènements" className='flex flex-col items-center'>
                        {renderFilterSection()}
                        {filterData(evenementsData).map(item => renderItem(item, 'evenement'))}
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
                            onClick={() => setShowCreateDialog(false)}
                        >
                            <motion.div
                                initial={{ y: 20 }}
                                animate={{ y: 0 }}
                                exit={{ y: 20 }}
                                className="bg-white rounded-lg shadow-xl w-full max-w-3xl"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="bg-blue-500 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                                    <h1 className="text-xl font-semibold">Créer un nouvel agenda</h1>
                                    <button
                                        className="text-white hover:bg-blue-600 rounded-full px-2 py-1 items-center justify-center"
                                        onClick={() => setShowCreateDialog(false)}
                                    >
                                        <i className="pi pi-times"></i>
                                    </button>
                                </div>

                                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                                        <div className='w-60'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Type*
                                            </label>
                                            <Dropdown
                                                name="type"
                                                value={agendaForm.type}
                                                onChange={handleFormChange}
                                                options={typeOptions}
                                                optionLabel="label"
                                                className="w-full"
                                            />
                                        </div>

                                        <div className='w-80'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Titre*
                                            </label>
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
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Date*
                                            </label>
                                            <Calendar
                                                value={agendaForm.date}
                                                onChange={handleDateChange}
                                                className="w-full"
                                                dateFormat="dd/mm/yy"
                                                showIcon
                                                required
                                            />
                                        </div>

                                        <div className='w-80'>
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Lien (URL)
                                            </label>
                                            <InputText
                                                name="lien"
                                                value={agendaForm.lien}
                                                onChange={handleFormChange}
                                                className="w-full"
                                                placeholder="https://example.com"
                                            />
                                        </div>

                                        <div className="w-full">
                                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                                Description*
                                            </label>
                                            <InputTextarea
                                                name="description"
                                                value={agendaForm.description}
                                                onChange={handleFormChange}
                                                rows={4}
                                                className="w-full"
                                                placeholder="Entrez la description"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <div className="flex justify-end pt-4 gap-2">
                                        <Button
                                            type="button"
                                            label="Annuler"
                                            severity="secondary"
                                            onClick={() => setShowCreateDialog(false)}
                                        />
                                        <Button
                                            type="submit"
                                            label="Enregistrer"
                                            severity="success"
                                        />
                                    </div>
                                </form>
                            </motion.div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </LayoutAdmin >
    );
}