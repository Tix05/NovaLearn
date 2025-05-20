import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { TabView, TabPanel } from 'primereact/tabview';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { ProgressSpinner } from 'primereact/progressspinner';
import { RadioButton } from 'primereact/radiobutton';
import { Toast } from 'primereact/toast';
import { debounce } from 'lodash';
import Layout from '../../components/Layout';
import {
    getStudentAgenda,
    fetchExamQuestions,
    startExam,
    submitExam,
    abandonExam,
    saveExamProgress,
    getExamStatus,
    getExamTime
} from '../../Services/agendaService';

const useExamTimer = (examId, initialDuration) => {
    const [timeLeft, setTimeLeft] = useState(initialDuration);
    const lastSyncRef = useRef(null);
    const isMounted = useRef(true);

    const syncWithServer = useCallback(async () => {
        if (!examId) return;

        try {
            const response = await getExamTime(examId);
            console.debug('Sync server response:', {
                examId,
                temps_restant: response.temps_restant,
                statut: response.statut,
                derniere_activite: response.derniere_activite
            });

            if (response.statut !== 'EN_COURS') {
                console.debug('Examen non EN_COURS, mise à 0 du timer:', response.statut);
                setTimeLeft(0);
                return;
            }

            const serverTime = response.temps_restant;
            if (serverTime === undefined || serverTime < 0) {
                console.warn('Temps restant invalide du serveur:', serverTime);
                return;
            }

            setTimeLeft(serverTime);
            lastSyncRef.current = Date.now();

            localStorage.setItem(`examTime_${examId}`, JSON.stringify({
                time: serverTime,
                lastSync: Date.now(),
                serverTime
            }));
        } catch (error) {
            console.error('Erreur de synchronisation:', error);
            const cached = localStorage.getItem(`examTime_${examId}`);
            if (cached) {
                const { time, lastSync } = JSON.parse(cached);
                const elapsed = Math.floor((Date.now() - lastSync) / 1000);
                const adjustedCachedTime = Math.max(0, time - elapsed);
                console.debug('Restauration depuis cache:', { time, elapsed, adjustedCachedTime });
                setTimeLeft(adjustedCachedTime);
            }
        }
    }, [examId]);

    useEffect(() => {
        let timer;
        const updateTimer = () => {
            setTimeLeft(prev => {
                if (prev <= 1) {
                    console.debug('Temps écoulé, arrêt du timer');
                    clearInterval(timer);
                    return 0;
                }
                console.debug('Décrémentation timeLeft:', prev - 1);
                return prev - 1;
            });
        };

        if (timeLeft > 0) {
            timer = setInterval(updateTimer, 1000);
        }

        return () => {
            console.debug('Nettoyage timer');
            clearInterval(timer);
        };
    }, [timeLeft]);

    useEffect(() => {
        isMounted.current = true;
        syncWithServer();

        const syncInterval = setInterval(() => {
            if (isMounted.current) {
                console.debug('Synchronisation périodique pour examId:', examId);
                syncWithServer();
            }
        }, 10000);

        return () => {
            isMounted.current = false;
            console.debug('Nettoyage intervalle synchronisation');
            clearInterval(syncInterval);
        };
    }, [examId, syncWithServer]);

    useEffect(() => {
        if (!examId || timeLeft <= 0) return;

        const saveInterval = setInterval(async () => {
            try {
                console.debug('Sauvegarde automatique:', { examId, timeLeft });
                await saveExamProgress(examId, {}, timeLeft);
                localStorage.setItem(`examTimeLeft_${examId}`, timeLeft);
                localStorage.setItem(`examLastSave_${examId}`, new Date().toISOString());
            } catch (error) {
                console.error('Échec sauvegarde automatique:', error);
            }
        }, 10000);

        return () => {
            console.debug('Nettoyage intervalle sauvegarde');
            clearInterval(saveInterval);
        };
    }, [examId, timeLeft]);

    return [timeLeft, setTimeLeft];
};

const Agenda = () => {
    const navigate = useNavigate();
    const toast = useRef(null);
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
    const [examStarted, setExamStarted] = useState(false);
    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [confirmAction, setConfirmAction] = useState(null);
    const [confirmMessage, setConfirmMessage] = useState('');

    const [timeLeft, setTimeLeft] = useExamTimer(selectedExam?.id, selectedExam?.duration || 3600);

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

                const examensWithStatus = await Promise.all(
                    data.examens.map(async (exam) => {
                        try {
                            const statusData = await getExamStatus(exam.examenId);
                            return {
                                ...exam,
                                statut: statusData.statut || 'DISPONIBLE',
                                temps_restant: statusData.temps_restant || exam.duree || 3600
                            };
                        } catch (error) {
                            return {
                                ...exam,
                                statut: 'DISPONIBLE',
                                temps_restant: exam.duree || 3600
                            };
                        }
                    })
                );

                setAgendaData({
                    cours: data.cours || [],
                    examens: examensWithStatus,
                    evenements: data.evenements || [],
                });

                const ongoingExamId = localStorage.getItem('ongoingExamId');
                if (ongoingExamId) {
                    try {
                        const examData = await getExamStatus(ongoingExamId);
                        if (examData.statut === 'EN_COURS') {
                            const exam = examensWithStatus.find(e => e.examenId === parseInt(ongoingExamId));
                            if (exam) {
                                const examDetails = await fetchExamQuestions(ongoingExamId);

                                setSelectedExam({
                                    ...exam,
                                    id: ongoingExamId,
                                    duration: examDetails.duree || 3600,
                                    questions: examDetails.questions || [],
                                });
                                setExamStarted(true);
                                setExamAnswers(examData.reponses || {});
                                setExamQuestions(examDetails.questions || []);
                                setShowExamDialog(true);
                                setShowInstructions(false);
                            }
                        }
                    } catch (error) {
                        console.error('Erreur restauration examen:', error);
                        localStorage.removeItem('ongoingExamId');
                        localStorage.removeItem(`examTimeLeft_${ongoingExamId}`);
                        localStorage.removeItem(`examLastSave_${ongoingExamId}`);
                    }
                }
                setLoading(false);
            } catch (error) {
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

    const debouncedSaveProgress = useRef(
        debounce(async (examId, answers, tempsRestant) => {
            try {
                await saveExamProgress(examId, answers, tempsRestant);
                localStorage.setItem(`examTimeLeft_${examId}`, tempsRestant);
                localStorage.setItem(`examLastSave_${examId}`, new Date().toISOString());
            } catch (error) {
                console.error('Erreur sauvegarde progression:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Erreur lors de la sauvegarde de la progression',
                    life: 3000,
                });
            }
        }, 2000)
    ).current;

    const filterData = (data) => {
        if (periodeFilter === 'Tout') return data;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const endDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine':
                endDate.setDate(today.getDate() + 7);
                break;
            case 'Mois':
                endDate.setMonth(today.getMonth() + 1);
                break;
            case 'Trimestre':
                endDate.setMonth(today.getMonth() + 3);
                break;
            case 'Semestre':
                endDate.setMonth(today.getMonth() + 6);
                break;
            default:
                return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            itemDate.setHours(0, 0, 0, 0);
            return itemDate >= today && itemDate <= endDate;
        });
    };

    const handleExamClick = async (exam) => {
        try {
            const examData = await fetchExamQuestions(exam.examenId);
            setExamQuestions(examData.questions || []);
            setSelectedExam({
                ...exam,
                duration: examData.duree || 3600,
                questions: examData.questions || [],
                id: exam.examenId,
            });

            if (examData.statut === 'EN_COURS') {
                setExamAnswers(examData.reponses || {});
                setTimeLeft(examData.temps_restant);
                setExamStarted(true);
                setShowInstructions(false);
                localStorage.setItem('ongoingExamId', exam.examenId);
                localStorage.setItem(`examTimeLeft_${exam.examenId}`, examData.temps_restant);
            } else if (examData.statut === 'SOUMIS' || examData.statut === 'ABANDONNE') {
                toast.current.show({
                    severity: 'warn',
                    summary: 'Examen terminé',
                    detail: 'Cet examen a déjà été soumis ou abandonné.',
                    life: 3000,
                });
                return;
            } else {
                setExamAnswers({});
                setTimeLeft(examData.duree || 3600);
                setExamStarted(false);
                setShowInstructions(true);
            }

            setExamSubmitted(false);
            setShowExamDialog(true);
        } catch (error) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de charger les données de l\'examen.',
                life: 3000,
            });
        }
    };

    const handleStartExam = async () => {
        try {
            const response = await startExam(selectedExam.id);
            setShowInstructions(false);
            setTimeLeft(response.temps_restant || selectedExam.duration);
            setExamAnswers(response.reponses || {});
            setExamStarted(true);
            localStorage.setItem('ongoingExamId', selectedExam.id);
            localStorage.setItem(`examTimeLeft_${selectedExam.id}`, response.temps_restant || selectedExam.duration);
            localStorage.setItem(`examLastSave_${selectedExam.id}`, new Date().toISOString());
            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: response.message || 'Examen démarré',
                life: 3000,
            });
        } catch (error) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Erreur lors du démarrage de l\'examen',
                life: 3000,
            });
        }
    };

    const handleAnswerChange = useCallback((questionId, value) => {
        setExamAnswers(prev => {
            const newAnswers = { ...prev, [questionId]: value };
            if (selectedExam) {
                debouncedSaveProgress(selectedExam.id, newAnswers, timeLeft);
            }
            return newAnswers;
        });
    }, [selectedExam, timeLeft, debouncedSaveProgress]);

    const handleSubmitExam = async (autoSubmit = false) => {
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
        setConfirmMessage('Êtes-vous sûr de vouloir abandonner l\'examen ? Vos réponses ne seront pas enregistrées.');
        setConfirmAction('abandon');
        setShowConfirmDialog(true);
    };

    const handleConfirmAction = async () => {
        setShowConfirmDialog(false);
        if (confirmAction === 'submit' || confirmAction === 'submit-auto') {
            try {
                await debouncedSaveProgress.flush();
                const response = await submitExam(selectedExam.id, examAnswers);
                setExamSubmitted(true);
                setExamStarted(false);
                setAgendaData(prev => ({
                    ...prev,
                    examens: prev.examens.filter(e => e.examenId !== selectedExam.id),
                }));
                localStorage.removeItem('ongoingExamId');
                localStorage.removeItem(`examTimeLeft_${selectedExam.id}`);
                localStorage.removeItem(`examLastSave_${selectedExam.id}`);
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
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de la soumission de l\'examen',
                    life: 3000,
                });
            }
        } else if (confirmAction === 'abandon') {
            try {
                await abandonExam(selectedExam.id);
                setShowExamDialog(false);
                setExamStarted(false);
                setAgendaData(prev => ({
                    ...prev,
                    examens: prev.examens.filter(e => e.examenId !== selectedExam.id),
                }));
                localStorage.removeItem('ongoingExamId');
                localStorage.removeItem(`examTimeLeft_${selectedExam.id}`);
                localStorage.removeItem(`examLastSave_${selectedExam.id}`);
                toast.current.show({
                    severity: 'warn',
                    summary: 'Abandon',
                    detail: 'Vous avez abandonné l\'examen',
                    life: 3000,
                });
                navigate('/etudiant/agenda');
            } catch (error) {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.response?.data?.message || 'Erreur lors de l\'abandon de l\'examen',
                    life: 3000,
                });
            }
        }
    };

    const handleCancelConfirm = () => {
        setShowConfirmDialog(false);
        setConfirmAction(null);
        setConfirmMessage('');
    };

    const handleDialogClose = () => {
        if (examStarted && !examSubmitted) {
            toast.current.show({
                severity: 'warn',
                summary: 'Attention',
                detail: 'Vous ne pouvez pas quitter l\'examen sans soumettre ou abandonner.',
                life: 3000,
            });
            return;
        }
        setShowExamDialog(false);
        setSelectedExam(null);
        setShowInstructions(true);
        setExamAnswers({});
        setExamSubmitted(false);
        setTimeLeft(0);
        setExamStarted(false);
        localStorage.removeItem('ongoingExamId');
        localStorage.removeItem(`examTimeLeft_${selectedExam?.id}`);
        localStorage.removeItem(`examLastSave_${selectedExam?.id}`);
    };

    const formatTime = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    const renderItem = (item, isExam = false) => (
        <div
            key={item.examenId || item.id}
            className={`flex flex-col md:flex-row border-[1px] mb-4 w-full md:w-4/5 rounded-lg bg-white shadow-md ${isExam ? 'hover:shadow-lg transition-shadow cursor-pointer' : ''}`}
            onClick={isExam ? () => handleExamClick(item) : undefined}
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
                        {item.statut === 'EN_COURS' ? 'EN COURS' : item.statut === 'SOUMIS' ? 'SOUMIS' : item.statut === 'ABANDONNE' ? 'ABANDONNÉ' : item.type}
                    </span>
                </div>

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

                <div className="mt-3 pt-2 border-t border-gray-200">
                    <p className="text-sm font-medium text-gray-700">
                        Publié par: {item.nom_auteur}
                    </p>
                    {isExam && (
                        <div className="mt-2">
                            {item.statut === 'EN_COURS' ? (
                                <Link
                                    to="#"
                                    onClick={(e) => { e.preventDefault(); handleExamClick(item); }}
                                    className="text-green-600 hover:underline"
                                >
                                    Reprendre l'examen
                                </Link>
                            ) : item.statut === 'SOUMIS' || item.statut === 'ABANDONNE' ? (
                                <span className="text-gray-500">Terminé</span>
                            ) : (
                                <Link
                                    to="#"
                                    onClick={(e) => { e.preventDefault(); handleExamClick(item); }}
                                    className="text-blue-600 hover:underline"
                                >
                                    Participer à l'examen
                                </Link>
                            )}
                        </div>
                    )}
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
            {selectedExam?.questions.length === 0 && (
                <p className="mb-3 text-red-500"><strong>Erreur :</strong> Aucune question disponible. Veuillez contacter l'administrateur pour vérifier les données de l'examen.</p>
            )}
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
                                <span className="text-sm text-gray-500 ml-2">({question.points} point{question.points > 1 ? 's' : ''})</span>
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
                                                {option.text}
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
                    <p className="text-red-500">Aucune question disponible pour cet examen. Veuillez contacter l'administrateur pour vérifier les données.</p>
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
                    disabled={selectedExam?.questions?.length === 0}
                />
            </div>
        </div>
    );

    const renderConfirmDialog = () => (
        <Dialog
            visible={showConfirmDialog}
            onHide={handleCancelConfirm}
            header={confirmAction === 'abandon' ? 'Confirmation d\'abandon' : 'Confirmation de soumission'}
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