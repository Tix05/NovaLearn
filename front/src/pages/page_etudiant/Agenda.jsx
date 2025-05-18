import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { TabView, TabPanel } from 'primereact/tabview';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { ProgressSpinner } from 'primereact/progressspinner';
import { RadioButton } from 'primereact/radiobutton';
import { Toast } from 'primereact/toast';
import Layout from '../../components/Layout';
import { getStudentAgenda } from '../../Services/agendaService';
import axios from 'axios';

const Agenda = () => {
    const navigate = useNavigate();
    const toast = React.useRef(null);
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const [agendaData, setAgendaData] = useState({
        cours: [],
        examens: [],
        evenements: []
    });
    const [loading, setLoading] = useState(true);
    const [selectedExam, setSelectedExam] = useState(null);
    const [examQuestions, setExamQuestions] = useState([]);
    const [showExamDialog, setShowExamDialog] = useState(false);
    const [showInstructions, setShowInstructions] = useState(true);
    const [examAnswers, setExamAnswers] = useState({});
    const [examSubmitted, setExamSubmitted] = useState(false);
    const [timeLeft, setTimeLeft] = useState(0);
    const [examStarted, setExamStarted] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [confirmMessage, setConfirmMessage] = useState('');

    const periodeOptions = [
        { label: 'Tout', value: 'Tout' },
        { label: 'Semaine', value: 'Semaine' },
        { label: 'Mois', value: 'Mois' },
        { label: 'Trimestre', value: 'Trimestre' },
        { label: 'Semestre', value: 'Semestre' },
    ];

    useEffect(() => {
        const fetchAgendaData = async () => {
            try {
                const data = await getStudentAgenda();
                setAgendaData({
                    cours: data.cours,
                    examens: data.examens.map(exam => ({
                        ...exam,
                        hasLink: !!exam.url,
                    })),
                    evenements: data.evenements,
                });
                setLoading(false);
            } catch (error) {
                console.error('Erreur lors du chargement de l\'agenda:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la récupération de l\'agenda',
                    life: 3000,
                });
                setLoading(false);
            }
        };
        fetchAgendaData();
    }, []);

    // Charger les questions de l'examen lorsque l'examen est sélectionné
    const fetchExamQuestions = async (examId) => {
        try {
            const response = await axios.get(`http://localhost:8000/api/examen/${examId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            setExamQuestions(response.data.questions);
            return response.data;
        } catch (error) {
            console.error('Erreur lors de la récupération des questions:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Erreur lors de la récupération des questions de l\'examen',
                life: 3000,
            });
            return null;
        }
    };

    // Gestion du timer
    useEffect(() => {
        let timer;
        if (examStarted && timeLeft > 0) {
            timer = setInterval(() => {
                setTimeLeft(prev => {
                    if (prev <= 1) {
                        clearInterval(timer);
                        handleSubmitExam(true); // Soumission automatique
                        return 0;
                    }
                    return prev - 1;
                });
            }, 1000);
        }
        return () => clearInterval(timer);
    }, [examStarted, timeLeft]);

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

    const handleExamClick = async (exam) => {
        if (exam.hasLink) {
            console.log('Examen cliqué:', exam);
            const examData = await fetchExamQuestions(exam.id);
            if (examData) {
                setSelectedExam({ ...exam, duration: examData.duree, questions: examData.questions });
                setExamAnswers({});
                setExamSubmitted(false);
                setShowInstructions(true);
                setTimeLeft(0);
                setExamStarted(false);
                setShowExamDialog(true);
            }
        }
    };

    const handleStartExam = () => {
        console.log('Début de l\'examen');
        setShowInstructions(false);
        setTimeLeft(selectedExam.duration || 3600);
        setExamStarted(true);
    };

    const handleAnswerChange = (questionId, value) => {
        console.log('Réponse modifiée:', { questionId, value });
        setExamAnswers(prev => ({
            ...prev,
            [questionId]: value,
        }));
    };

    const handleSubmitExam = async (autoSubmit = false) => {
        console.log('Initiation de la soumission de l\'examen');
        if (autoSubmit) {
            setConfirmMessage('Le temps est écoulé. Vos réponses vont être soumises automatiquement.');
            setConfirmAction('submit-auto');
        } else {
            setConfirmMessage('Êtes-vous sûr de vouloir soumettre vos réponses ?');
            setConfirmAction('submit');
        }
        setShowConfirmDialog(true);
    };

    const handleAbandonExam = () => {
        console.log('Initiation de l\'abandon de l\'examen');
        setConfirmMessage('Êtes-vous sûr de vouloir abandonner l’examen ? Vos réponses ne seront pas enregistrées.');
        setConfirmAction('abandon');
        setShowConfirmDialog(true);
    };

    const handleConfirmAction = async () => {
        console.log('Action confirmée:', confirmAction);
        setShowConfirmDialog(false);
        if (confirmAction === 'submit' || confirmAction === 'submit-auto') {
            try {
                const response = await axios.post(
                    `http://localhost:8000/api/examen/${selectedExam.id}/submit`,
                    { answers: examAnswers },
                    {
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('token')}`,
                            'Content-Type': 'application/json',
                        }
                    }
                );
                console.log('Examen soumis:', response.data);
                setExamSubmitted(true);
                setExamStarted(false);
                setAgendaData(prev => ({
                    ...prev,
                    examens: prev.examens.filter(e => e.id !== selectedExam.id),
                }));
                toast.current.show({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Examen soumis avec succès',
                    life: 3000,
                });
                setTimeout(() => {
                    setShowExamDialog(false);
                    navigate('/etudiant/agenda');
                }, 3000);
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la soumission de l\'examen',
                    life: 3000,
                });
            }
        } else if (confirmAction === 'abandon') {
            console.log('Examen abandonné');
            setShowExamDialog(false);
            setExamStarted(false);
            setAgendaData(prev => ({
                ...prev,
                examens: prev.examens.filter(e => e.id !== selectedExam.id),
            }));
            toast.current.show({
                severity: 'warn',
                summary: 'Abandon',
                detail: 'Vous avez abandonné l\'examen',
                life: 3000,
            });
            navigate('/etudiant/agenda');
        }
    };

    const handleCancelConfirm = () => {
        console.log('Action annulée');
        setShowConfirmDialog(false);
        setConfirmAction(null);
        setConfirmMessage('');
    };

    const handleDialogClose = () => {
        console.log('Fermeture du dialogue des instructions');
        setShowExamDialog(false);
        setSelectedExam(null);
        setShowInstructions(true);
        setExamAnswers({});
        setExamSubmitted(false);
        setTimeLeft(0);
        setExamStarted(false);
    };

    const formatTime = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    const renderItem = (item, isExam = false) => (
        <div
            key={item.id}
            className={`flex flex-col md:flex-row border-[1px] mb-4 w-full md:w-4/5 rounded-lg bg-white shadow-md ${isExam && item.hasLink ? 'hover:shadow-lg transition-shadow cursor-pointer' : ''}`}
            onClick={isExam && item.hasLink ? () => handleExamClick(item) : undefined}
        >
            <div className="flex flex-col p-4 text-center md:w-28 flex-shrink-0">
                <h1 className="text-5xl font-bold text-gray-800">{new Date(item.date).getDate()}</h1>
                <h2 className="text-lg font-semibold text-gray-700 uppercase">
                    {new Date(item.date).toLocaleString('default', { month: 'short' })}
                </h2>
                <h3 className="text-sm text-gray-500">
                    {new Date(item.date).toLocaleString('default', { weekday: 'short' })}
                </h3>
            </div>

            <Divider layout="vertical" className="hidden md:block h-auto" />

            <div className="flex flex-col p-4 flex-1 min-h-[180px]">
                <div className="flex justify-between items-start mb-2">
                    <h1 className="text-xl font-semibold text-gray-800 truncate flex-1">
                        {item.titre}
                    </h1>
                    <span className={`text-xs px-2 py-1 rounded ml-2 ${item.type === 'EXAMEN' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`}>
                        {item.type}
                    </span>
                </div>

                {isExam && item.hasLink && (
                    <div className="mb-2 flex items-center">
                        <i className="pi pi-link mr-2 text-blue-500"></i>
                        <span className="text-sm font-medium text-blue-600">
                            Cliquez pour accéder à l'examen
                        </span>
                    </div>
                )}

                <div className="mb-3">
                    <p className="text-gray-600 whitespace-pre-wrap break-words">
                        {item.description}
                    </p>
                    {item.mention && item.parcours && item.niveau && (
                        <p className="text-sm text-gray-500 mt-1">
                            {item.mention} - {item.parcours} - {item.niveau}
                        </p>
                    )}
                </div>

                {item.image && (
                    <div className="mt-3">
                        <img
                            src={item.image}
                            alt={item.titre}
                            className="w-full h-auto rounded-lg border border-gray-200"
                        />
                    </div>
                )}

                {item.video && (
                    <div className="mt-3">
                        <div className="relative pt-[56.25%] bg-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                            <video
                                controls
                                className="absolute inset-0 w-full h-full"
                            >
                                <source src={item.video} type="video/mp4" />
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        </div>
                    </div>
                )}

                <div className="mt-3 pt-2 border-t border-gray-100">
                    <p className="text-sm font-medium text-gray-700">
                        Publié par: {item.nom_auteur}
                    </p>
                </div>
            </div>
        </div>
    );

    const renderFilterSection = () => (
        <div className="mb-6 flex gap-2 w-60">
            <Dropdown
                value={periodeFilter}
                onChange={(e) => setPeriodeFilter(e.value)}
                options={periodeOptions}
                optionLabel="label"
                placeholder="Période"
                panelClassName="font-poppins text-sm"
                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
            />
        </div>
    );

    const renderInstructions = () => (
        <div className="p-5 flex flex-col h-full">
            <h2 className="text-2xl font-bold mb-4">{selectedExam?.titre}</h2>
            <div className="mb-4 p-3 bg-blue-50 border-round">
                <div className="font-semibold">
                    <i className="pi pi-clock mr-2"></i>
                    Durée de l'examen: {Math.floor(selectedExam?.duration / 60)} minutes
                </div>
            </div>
            <p className="mb-3"><strong>Nombre de questions :</strong> {selectedExam?.questions.length}</p>
            <p className="mb-3"><strong>Instructions :</strong></p>
            <ul className="list-disc ml-5 mb-4 flex-1">
                <li>Lisez attentivement chaque question avant de répondre.</li>
                <li>Pour les questions à choix multiples, sélectionnez une seule réponse.</li>
                <li>Pour les questions ouvertes, fournissez une réponse détaillée.</li>
                <li>Vous ne pouvez pas quitter l'examen sans soumettre ou abandonner.</li>
                <li>Le temps commencera lorsque vous cliquez sur "Participer".</li>
            </ul>
            <div className="flex justify-center">
                <Button
                    label="Participer"
                    icon="pi pi-play"
                    className="p-button-raised p-button-success w-40"
                    onClick={handleStartExam}
                />
            </div>
        </div>
    );

    const renderExam = () => (
        <div className="exam-container p-5 flex flex-col h-full">
            <div className="mb-4 p-3 bg-blue-50 border-round flex justify-between items-center">
                <div className="font-semibold">
                    <i className="pi pi-clock mr-2"></i>
                    Temps restant: <span className="text-red-500">{formatTime(timeLeft)}</span>
                </div>
            </div>

            <div className="questions-container flex-1 overflow-y-auto">
                {selectedExam?.questions?.length > 0 ? (
                    selectedExam.questions.map((question, index) => (
                        <div key={question.id} className="mb-5 p-3 border-1 border-round surface-card">
                            <h3 className="text-lg font-medium mb-3">
                                Question {index + 1}: {question.text}
                            </h3>

                            {question.type === 'radio' ? (
                                <div className="flex flex-col gap-2">
                                    {question.options.map(option => (
                                        <div key={option.value} className="flex items-center">
                                            <RadioButton
                                                inputId={`${question.id}-${option.value}`}
                                                name={`question-${question.id}`}
                                                value={option.value}
                                                onChange={(e) => handleAnswerChange(question.id, e.value)}
                                                checked={examAnswers[question.id] === option.value}
                                            />
                                            <label htmlFor={`${question.id}-${option.value}`} className="ml-2">
                                                {option.label}
                                            </label>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <textarea
                                    rows={5}
                                    className="w-full p-2 border-1 border-round"
                                    value={examAnswers[question.id] || ''}
                                    onChange={(e) => handleAnswerChange(question.id, e.target.value)}
                                    placeholder="Développez votre réponse..."
                                />
                            )}
                        </div>
                    ))
                ) : (
                    <p className="text-gray-500">Aucune question disponible pour cet examen.</p>
                )}
            </div>

            <div className="flex justify-end space-x-5 mt-4">
                <Button
                    label="Abandonner"
                    icon="pi pi-times"
                    className="p-button-raised p-button-warning w-60"
                    onClick={handleAbandonExam}
                />
                <Button
                    label="Soumettre l'examen"
                    icon="pi pi-send"
                    className="p-button-raised p-button-danger w-60"
                    onClick={() => handleSubmitExam(false)}
                />
            </div>
        </div>
    );

    const renderConfirmDialog = () => (
        <Dialog
            visible={showConfirmDialog}
            onHide={handleCancelConfirm}
            header={confirmAction === 'abandon' ? 'Confirmation d’abandon' : 'Confirmation de soumission'}
            modal
            style={{ width: '400px' }}
            footer={
                <div>
                    <Button
                        label="Non"
                        icon="pi pi-times"
                        className="p-button-text"
                        onClick={handleCancelConfirm}
                    />
                    <Button
                        label="Oui"
                        icon="pi pi-check"
                        className="p-button-raised p-button-danger"
                        onClick={handleConfirmAction}
                    />
                </div>
            }
        >
            <div className="p-4">
                <p>{confirmMessage}</p>
            </div>
        </Dialog>
    );

    if (loading) {
        return (
            <Layout>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <Toast ref={toast} />
            <div className="card custom-scrollbar h-[90vh] overflow-y-auto">
                <TabView className="custom-tabview">
                    <TabPanel header="Cours" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.cours).length > 0 ? (
                                filterData(agendaData.cours).map(item => renderItem(item))
                            ) : (
                                <p className="text-gray-500">Aucun cours prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>

                    <TabPanel header="Examens" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.examens).length > 0 ? (
                                filterData(agendaData.examens).map(item => renderItem(item, true))
                            ) : (
                                <p className="text-gray-500">Aucun examen prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>

                    <TabPanel header="Evènements" className="flex flex-col items-center">
                        {renderFilterSection()}
                        <div className="w-full flex flex-col items-center">
                            {filterData(agendaData.evenements).length > 0 ? (
                                filterData(agendaData.evenements).map(item => renderItem(item))
                            ) : (
                                <p className="text-gray-500">Aucun évènement prévu pour cette période</p>
                            )}
                        </div>
                    </TabPanel>
                </TabView>

                <Dialog
                    visible={showExamDialog}
                    onHide={handleDialogClose}
                    style={{ width: '100vw', height: '100vh', maxWidth: '100%', maxHeight: '100%' }}
                    header={selectedExam?.titre || "Examen"}
                    modal
                    className="p-fluid"
                    closable={showInstructions && !examSubmitted}
                    contentStyle={{ height: '100%', display: 'flex', flexDirection: 'column' }}
                >
                    {examSubmitted ? (
                        <div className="text-center p-5 flex flex-col justify-center h-full">
                            <i className="pi pi-check-circle text-6xl text-green-500 mb-4"></i>
                            <h2 className="text-2xl font-bold mb-2">Examen soumis avec succès!</h2>
                            <p>Votre copie est en cours de correction.</p>
                            <p>Vous recevrez votre note sous peu.</p>
                        </div>
                    ) : showInstructions ? (
                        renderInstructions()
                    ) : (
                        renderExam()
                    )}
                </Dialog>
                {renderConfirmDialog()}
            </div>
        </Layout>
    );
};

export default Agenda;