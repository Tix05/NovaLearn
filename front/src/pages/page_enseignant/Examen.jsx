import React, { useState, useEffect, useRef } from 'react';
import { Upload, Notebook as Robot, Send, FileText, Database, CheckCircle } from 'lucide-react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { useParams } from 'react-router-dom';
import { Toast } from 'primereact/toast';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MultiSelect } from 'primereact/multiselect';
import { InputNumber } from 'primereact/inputnumber';
import { createExamen, getEcSupports, submitExamenToAdmin } from '../../Services/examenService';

function Examen() {
    const { mentionId, semestreId, coursId } = useParams();
    const toast = useRef(null);
    const [selectedFile, setSelectedFile] = useState(null);
    const [generatedFile, setGeneratedFile] = useState(null);
    const [generatedFileUrl, setGeneratedFileUrl] = useState(null);
    const [courseContent, setCourseContent] = useState('');
    const [titre, setTitre] = useState('Examen');
    const [description, setDescription] = useState('');
    const [hours, setHours] = useState(1);
    const [minutes, setMinutes] = useState(0);
    const [mode, setMode] = useState('upload');
    const [aiMode, setAiMode] = useState('existing');
    const [isGenerating, setIsGenerating] = useState(false);
    const [supports, setSupports] = useState([]);
    const [selectedSupports, setSelectedSupports] = useState([]);
    const [loadingSupports, setLoadingSupports] = useState(false);
    const [error, setError] = useState(null);
    const [tempFile, setTempFile] = useState(null);
    const [questions, setQuestions] = useState([]);
    const [errorMessage, setErrorMessage] = useState('');

    useEffect(() => {
        if (aiMode === 'existing' && coursId) {
            const fetchSupports = async () => {
                setLoadingSupports(true);
                try {
                    const data = await getEcSupports(coursId);
                    setSupports(Array.isArray(data) ? data.filter(s => s.type === 'document' && s.fichier && s.fichier.endsWith('.pdf')) : []);
                } catch (err) {
                    setError(err.message);
                    toast.current.show({
                        severity: 'error',
                        summary: 'Erreur',
                        detail: err.message,
                        life: 3000,
                    });
                } finally {
                    setLoadingSupports(false);
                }
            };
            fetchSupports();
        }
    }, [aiMode, coursId]);

    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (file.type !== 'application/pdf') {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez sélectionner un fichier PDF',
                    life: 3000,
                });
                return;
            }
            setSelectedFile(file);
            setGeneratedFileUrl(URL.createObjectURL(file));
            setErrorMessage('');
        } else {
            setSelectedFile(null);
            setGeneratedFileUrl(null);
            setErrorMessage('');
        }
    };

    const handleGeneratedFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (file.type !== 'application/pdf') {
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Veuillez sélectionner un fichier PDF',
                    life: 3000,
                });
                return;
            }
            setGeneratedFile(file);
            setGeneratedFileUrl(URL.createObjectURL(file));
            setErrorMessage('');
        }
    };

    const resetForm = (isAiMode) => {
        if (isAiMode) {
            setSelectedFile(null);
            setGeneratedFile(null);
            setGeneratedFileUrl(null);
            setCourseContent('');
            setTitre('Examen');
            setDescription('');
            setHours(1);
            setMinutes(0);
            setSelectedSupports([]);
            setTempFile(null);
            setQuestions([]);
            setErrorMessage('');
        } else {
            setSelectedFile(null);
            setGeneratedFileUrl(null);
            setTitre('Examen');
            setDescription('');
            setHours(1);
            setMinutes(0);
            setErrorMessage('');
        }
    };

    const calculateDurationInSeconds = () => {
        return (hours * 3600) + (minutes * 60);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (mode === 'ai' && aiMode === 'existing' && selectedSupports.length === 0) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez sélectionner au moins un support',
                life: 3000,
            });
            return;
        }
        if (mode === 'ai' && aiMode === 'new' && !selectedFile) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez sélectionner un fichier PDF',
                life: 3000,
            });
            return;
        }
        if (mode === 'upload' && !selectedFile) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez sélectionner un fichier PDF',
                life: 3000,
            });
            return;
        }
        if (!titre.trim()) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez entrer un titre pour l\'examen',
                life: 3000,
            });
            return;
        }
        const durationInSeconds = calculateDurationInSeconds();
        if (durationInSeconds <= 0) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez entrer une durée valide (au moins 1 minute)',
                life: 3000,
            });
            return;
        }

        setIsGenerating(true);
        try {
            let submitInstructions = courseContent.trim();
            if (aiMode === 'existing' && selectedSupports.length > 0) {
                const supportTitles = selectedSupports.map((s) => s.titre).join(', ');
                submitInstructions = `Utiliser les supports: ${supportTitles}\n${submitInstructions}`;
            }
            if (mode === 'upload') {
                submitInstructions = 'À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.';
            }

            const response = await createExamen(
                coursId,
                titre,
                description,
                courseContent.trim(),
                mode === 'upload' ? 'pdf' : 'ia_genere',
                submitInstructions,
                mode === 'ai' && aiMode === 'existing' && selectedSupports.length > 0 ? null : selectedFile,
                durationInSeconds
            );

            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Examen prêt pour envoi à l\'administration',
                life: 3000,
            });

            setGeneratedFile(new File([new Blob()], 'examen_generé.pdf', { type: 'application/pdf' }));
            if (response.temp_file) {
                const fileUrl = `http://localhost:8000${response.temp_file}`;
                setGeneratedFileUrl(fileUrl);
                setTempFile(response.temp_file);
            }
            if (response.questions) {
                setQuestions(response.questions);
            }
            setTitre(response.titre);
            setDescription(response.description);
        } catch (error) {
            setErrorMessage(error.message);
        } finally {
            setIsGenerating(false);
        }
    };

    const handleSendToAdmin = async () => {
        if (!selectedFile && !tempFile && !generatedFile) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Aucun fichier à envoyer',
                life: 3000,
            });
            return;
        }

        setIsGenerating(true);
        try {
            let submitInstructions = mode === 'ai' ? courseContent.trim() : 'À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.';
            if (mode === 'ai' && aiMode === 'existing' && selectedSupports.length > 0) {
                const supportTitles = selectedSupports.map((s) => s.titre).join(', ');
                submitInstructions = `Utiliser les supports: ${supportTitles}\n${submitInstructions}`;
            }

            const response = await submitExamenToAdmin(
                coursId,
                titre,
                description,
                courseContent.trim(),
                mode === 'upload' ? 'pdf' : 'ia_genere',
                submitInstructions,
                generatedFile || selectedFile,
                tempFile,
                questions,
                calculateDurationInSeconds()
            );

            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Examen envoyé à l\'administration',
                life: 3000,
            });

            if (response.erreurs_analyse) {
                setErrorMessage(response.erreurs_analyse);
            }

            resetForm(mode === 'ai');
        } catch (error) {
            setErrorMessage(error.message);
        } finally {
            setIsGenerating(false);
        }
    };

    const supportOptionTemplate = (option) => {
        return (
            <div className="flex items-center">
                <span>{option.titre} ({option.type})</span>
            </div>
        );
    };

    const selectedSupportTemplate = (option) => {
        if (option) {
            return (
                <div className="flex items-center">
                    <span>{option.titre}</span>
                </div>
            );
        }
        return <span>Choisir des supports</span>;
    };

    return (
        <LayoutEnseignant>
            <Toast ref={toast} position='bottom-right' />
            <div>
                <h1 className="text-3xl font-semibold text-gray-700 p-4">Création d'Examen</h1>
                <div className="w-full h-screen flex flex-col">
                    <div className="bg-white py-6 px-[10vw]">
                        <div className="flex gap-4 mb-6">
                            <button
                                onClick={() => { setMode('upload'); setErrorMessage(''); }}
                                className={`flex-1 py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-all ${mode === 'upload'
                                    ? 'bg-[#DC3545] text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                            >
                                <Upload size={20} />
                                <span>Télécharger PDF</span>
                            </button>
                            <button
                                onClick={() => { setMode('ai'); setErrorMessage(''); }}
                                className={`flex-1 py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-all ${mode === 'ai'
                                    ? 'bg-[#DC3545] text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                            >
                                <Robot size={20} />
                                <span>Création Assistée</span>
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-3">
                                <label className="block text-sm font-medium text-gray-700">
                                    Titre de l'examen
                                </label>
                                <input
                                    type="text"
                                    value={titre}
                                    onChange={(e) => setTitre(e.target.value)}
                                    className="w-full p-2 border rounded-md"
                                    placeholder="Entrez le titre de l'examen"
                                    required
                                />
                            </div>
                            <div className="space-y-3">
                                <label className="block text-sm font-medium text-gray-700">
                                    Description de l'examen
                                </label>
                                <textarea
                                    value={description}
                                    onChange={(e) => setDescription(e.target.value)}
                                    className="w-full h-20 p-2 border rounded-md resize-y"
                                    placeholder="Entrez une description de l'examen (facultatif)"
                                />
                            </div>
                            <div className="space-y-3">
                                <label className="block text-sm font-medium text-gray-700">
                                    Durée de l'examen
                                </label>
                                <div className="flex gap-4 pb-10">
                                    <div className="flex-1">
                                        <label className="block text-sm text-gray-600 mb-1">Heures</label>
                                        <InputNumber
                                            value={hours}
                                            onValueChange={(e) => setHours(e.value || 0)}
                                            min={0}
                                            max={23}
                                            showButtons
                                            className="w-full"
                                            inputClassName="p-2 border"
                                        />
                                    </div>
                                    <div className="flex-1">
                                        <label className="block text-sm text-gray-600 mb-1">Minutes</label>
                                        <InputNumber
                                            value={minutes}
                                            onValueChange={(e) => setMinutes(e.value || 0)}
                                            min={0}
                                            max={59}
                                            showButtons
                                            className="w-full"
                                            inputClassName="p-2 border"
                                        />
                                    </div>
                                </div>
                            </div>
                            {mode === 'upload' ? (
                                <div className="space-y-4">
                                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <input
                                            type="file"
                                            accept=".pdf"
                                            onChange={handleFileChange}
                                            className="hidden"
                                            id="file-upload"
                                        />
                                        <label
                                            htmlFor="file-upload"
                                            className="cursor-pointer flex flex-col items-center"
                                        >
                                            <Upload size={40} className="text-gray-400 mb-4" />
                                            <span className="text-gray-600">
                                                {selectedFile
                                                    ? selectedFile.name
                                                    : "Cliquez pour télécharger l'examen en PDF"}
                                            </span>
                                        </label>
                                    </div>
                                    {selectedFile && (
                                        <div className="space-y-4">
                                            <div className="mb-4">
                                                <p className="text-gray-600 mb-2">Prévisualisation du PDF :</p>
                                                <div className="h-64 overflow-y-auto border rounded">
                                                    <embed
                                                        src={generatedFileUrl}
                                                        type="application/pdf"
                                                        width="100%"
                                                        height="100%"
                                                        className="min-h-full"
                                                    />
                                                </div>
                                            </div>
                                            <div className="flex w-full items-center justify-center pt-4">
                                                <button
                                                    type="button"
                                                    onClick={handleSendToAdmin}
                                                    className="w-80 bg-[#28A745] text-white py-2 px-6 rounded-full hover:bg-[#299041] transition-colors flex items-center justify-center gap-2"
                                                    disabled={isGenerating}
                                                >
                                                    {isGenerating ? (
                                                        <ProgressSpinner style={{ width: '20px', height: '20px' }} className="white-spinner" />
                                                    ) : (
                                                        <>
                                                            <CheckCircle size={20} />
                                                            <span>Envoyer à l'administration</span>
                                                        </>
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                    <p className="text-gray-600 text-sm text-center mt-4">
                                        Instructions : À chaque question, précisez la réponse correcte parmi les options pour les questions de type radio, attribuez des points à chaque question, et fournissez la réponse exacte pour les questions ouvertes.
                                    </p>
                                    {errorMessage && (
                                        <p className="text-red-500 text-sm mt-4 mb-10">{errorMessage}</p>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4 items-center justify-center">
                                        <button
                                            type="button"
                                            onClick={() => setAiMode('existing')}
                                            className={`p-6 rounded-lg border-2 transition-all ${aiMode === 'existing'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200 hover:border-blue-200 hover:bg-gray-50'
                                                }`}
                                        >
                                            <Database
                                                size={32}
                                                className={`mx-auto mb-3 ${aiMode === 'existing' ? 'text-blue-500' : 'text-gray-400'
                                                    }`}
                                            />
                                            <h3
                                                className={`text-center font-medium ${aiMode === 'existing' ? 'text-blue-700' : 'text-gray-600'
                                                    }`}
                                            >
                                                Utiliser les supports existants
                                            </h3>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setAiMode('new')}
                                            className={`p-6 rounded-lg border-2 transition-all ${aiMode === 'new'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200 hover:border-blue-200 hover:bg-gray-50'
                                                }`}
                                        >
                                            <FileText
                                                size={32}
                                                className={`mx-auto mb-3 ${aiMode === 'new' ? 'text-blue-500' : 'text-gray-400'
                                                    }`}
                                            />
                                            <h3
                                                className={`text-center font-medium ${aiMode === 'new' ? 'text-blue-700' : 'text-gray-600'
                                                    }`}
                                            >
                                                Ajouter de nouveaux supports
                                            </h3>
                                        </button>
                                    </div>

                                    {aiMode === 'existing' ? (
                                        <div className="space-y-4">
                                            <label className="block text-sm font-medium text-gray-700">
                                                Sélectionner des supports existants
                                            </label>
                                            {loadingSupports ? (
                                                <ProgressSpinner style={{ width: '50px', height: '50px' }} />
                                            ) : error ? (
                                                <p className="text-red-500">{error}</p>
                                            ) : supports.length === 0 ? (
                                                <p className="text-gray-500">
                                                    Aucun support PDF disponible pour cet EC.
                                                </p>
                                            ) : (
                                                <MultiSelect
                                                    value={selectedSupports}
                                                    options={supports}
                                                    onChange={(e) => setSelectedSupports(e.value || [])}
                                                    optionLabel="titre"
                                                    placeholder="Choisir des supports"
                                                    maxSelectedLabels={3}
                                                    className="w-full"
                                                    itemTemplate={supportOptionTemplate}
                                                    selectedItemTemplate={selectedSupportTemplate}
                                                />
                                            )}
                                            <label className="block text-sm font-medium text-gray-700 mt-4">
                                                Instructions supplémentaires
                                            </label>
                                            <textarea
                                                value={courseContent}
                                                onChange={(e) => setCourseContent(e.target.value)}
                                                className="w-full h-24 p-2 border rounded-md resize-none"
                                                placeholder="Entrez vos instructions pour la génération de l'examen..."
                                            />
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            <label className="block text-sm font-medium text-gray-700">
                                                Ajouter des nouveaux supports de cours (PDF)
                                            </label>
                                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
                                                <input
                                                    type="file"
                                                    accept=".pdf"
                                                    onChange={handleFileChange}
                                                    className="hidden"
                                                    id="course-upload"
                                                />
                                                <label
                                                    htmlFor="course-upload"
                                                    className="cursor-pointer flex flex-col items-center"
                                                >
                                                    <Upload size={32} className="text-gray-400 mb-3" />
                                                    <span className="text-gray-600 text-center">
                                                        {selectedFile
                                                            ? selectedFile.name
                                                            : 'Cliquez pour ajouter vos supports de cours en PDF'}
                                                    </span>
                                                    <span className="text-sm text-gray-500 mt-1">
                                                        Vous pouvez sélectionner un fichier
                                                    </span>
                                                </label>
                                            </div>
                                            <label className="block text-sm font-medium text-gray-700">
                                                Instructions supplémentaires
                                            </label>
                                            <textarea
                                                value={courseContent}
                                                onChange={(e) => setCourseContent(e.target.value)}
                                                className="w-full p-2 border rounded-md"
                                                placeholder="Entrez vos instructions pour la génération de l'examen..."
                                            />
                                        </div>
                                    )}

                                    {!generatedFile ? (
                                        <div className="flex w-full items-center justify-center pt-4">
                                            <button
                                                type="submit"
                                                className="w-80 bg-[#DC3545] text-white py-2 px-6 rounded-full hover:bg-[#C82333] transition-colors flex items-center justify-center gap-2"
                                                disabled={isGenerating}
                                            >
                                                {isGenerating ? (
                                                    <ProgressSpinner style={{ width: '20px', height: '20px' }} className="white-spinner" />
                                                ) : (
                                                    <>
                                                        <Robot size={20} />
                                                        <span>{mode === 'upload' ? 'Prévisualiser' : 'Générer l\'examen'}</span>
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                                <p className="text-green-600 font-semibold mb-4">
                                                    Examen généré : {generatedFile.name}
                                                </p>
                                                {generatedFileUrl && (
                                                    <div className="mb-4">
                                                        <a
                                                            href={generatedFileUrl}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-blue-600 underline hover:text-blue-800"
                                                        >
                                                            Télécharger ou voir le PDF
                                                        </a>
                                                        <div className="h-64 overflow-y-auto border rounded mt-4">
                                                            <embed
                                                                src={generatedFileUrl}
                                                                type="application/pdf"
                                                                width="100%"
                                                                height="100%"
                                                                className="min-h-full"
                                                            />
                                                        </div>
                                                    </div>
                                                )}
                                                <input
                                                    type="file"
                                                    accept=".pdf"
                                                    onChange={handleGeneratedFileChange}
                                                    className="hidden"
                                                    id="generated-file-upload"
                                                />
                                                <label
                                                    htmlFor="generated-file-upload"
                                                    className="cursor-pointer flex flex-col items-center"
                                                >
                                                    <FileText size={40} className="text-gray-400 mb-4" />
                                                    <span className="text-gray-600">
                                                        Cliquez pour remplacer le PDF généré
                                                    </span>
                                                    <span className="text-sm text-gray-500 mt-2">
                                                        Vous pouvez remplacer le PDF généré si nécessaire
                                                    </span>
                                                </label>
                                            </div>
                                            <div className="flex w-full items-center justify-center pt-4">
                                                <button
                                                    type="button"
                                                    onClick={handleSendToAdmin}
                                                    className="w-80 bg-[#28A745] text-white py-2 px-6 rounded-full hover:bg-[#299041] transition-colors flex items-center justify-center gap-2"
                                                    disabled={isGenerating}
                                                >
                                                    {isGenerating ? (
                                                        <ProgressSpinner style={{ width: '20px', height: '20px' }} className="white-spinner" />
                                                    ) : (
                                                        <>
                                                            <CheckCircle size={20} />
                                                            <span>Envoyer à l'administration</span>
                                                        </>
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                    )}
                                    {errorMessage && (
                                        <p className="text-red-500 text-sm mt-4 mb-10">{errorMessage}</p>
                                    )}
                                </div>
                            )}
                        </form>
                    </div>
                </div>
            </div>
        </LayoutEnseignant>
    );
}

export default Examen;