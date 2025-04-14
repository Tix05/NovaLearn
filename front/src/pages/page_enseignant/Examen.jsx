import React, { useState } from 'react';
import { Upload, Notebook as Robot, Send, FileText, Database, CheckCircle } from 'lucide-react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

function Examen() {
    const [selectedFile, setSelectedFile] = useState(null);
    const [generatedFile, setGeneratedFile] = useState(null);
    const [courseContent, setCourseContent] = useState('');
    const [mode, setMode] = useState('upload');
    const [aiMode, setAiMode] = useState('existing');
    const [isGenerating, setIsGenerating] = useState(false);

    const handleFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            setSelectedFile(e.target.files[0]);
        }
    };

    const handleGeneratedFileChange = (e) => {
        if (e.target.files && e.target.files[0]) {
            setGeneratedFile(e.target.files[0]);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (mode === 'upload') {
            console.log('Envoi du PDF à l\'admin:', selectedFile);
            // Logique pour envoyer à l'admin
        } else {
            setIsGenerating(true);
            // Simulation de génération de PDF
            setTimeout(() => {
                console.log('Génération du PDF avec le contenu:', courseContent);
                setIsGenerating(false);
                setGeneratedFile(new File([], 'examen_generé.pdf'));
            }, 2000);
        }
    };

    const handleSendToAdmin = () => {
        console.log('Envoi du PDF généré à l\'admin:', generatedFile);
        // Logique pour envoyer à l'admin
    };

    return (
        <LayoutEnseignant>
            <div className="min-h-screen p-6">
                <h1 className="text-3xl font-semibold text-gray-700 mb-5">
                    Création d'Examen
                </h1>
                <div className="max-w-4xl mx-auto">
                    <div className="bg-white rounded-lg border-[1px] border-gray-400 shadow-lg p-6">
                        <div className="flex gap-4 mb-8">
                            <button
                                onClick={() => setMode('upload')}
                                className={`flex-1 py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-all ${mode === 'upload'
                                    ? 'bg-[#DC3545] text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                            >
                                <Upload size={20} />
                                <span>Télécharger PDF</span>
                            </button>
                            <button
                                onClick={() => setMode('ai')}
                                className={`flex-1 py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-all ${mode === 'ai'
                                    ? 'bg-[#DC3545] text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    }`}
                            >
                                <Robot size={20} />
                                <span>Création Assistée</span>
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            {mode === 'upload' ? (
                                <div className="space-y-6">
                                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
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
                                        <div className='flex w-full items-center justify-center pt-5'>
                                            <button
                                                type="submit"
                                                className="w-80 bg-[#28A745] text-white py-2 px-6 rounded-full hover:bg-[#299041] transition-colors flex items-center justify-center gap-2"
                                            >
                                                <CheckCircle size={20} />
                                                <span>Envoyer à l'administration</span>
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    {/* Onglets conservés */}
                                    <div className="grid grid-cols-2 gap-4 items-center justify-center">
                                        <button
                                            type="button"
                                            onClick={() => setAiMode('existing')}
                                            className={`p-6 rounded-lg border-2 transition-all ${aiMode === 'existing'
                                                ? 'border-blue-500 bg-blue-50'
                                                : 'border-gray-200 hover:border-blue-200 hover:bg-gray-50'
                                                }`}
                                        >
                                            <Database size={32} className={`mx-auto mb-3 ${aiMode === 'existing' ? 'text-blue-500' : 'text-gray-400'
                                                }`} />
                                            <h3 className={`text-center font-medium ${aiMode === 'existing' ? 'text-blue-700' : 'text-gray-600'
                                                }`}>
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
                                            <FileText size={32} className={`mx-auto mb-3 ${aiMode === 'new' ? 'text-blue-500' : 'text-gray-400'
                                                }`} />
                                            <h3 className={`text-center font-medium ${aiMode === 'new' ? 'text-blue-700' : 'text-gray-600'
                                                }`}>
                                                Ajouter de nouveaux supports
                                            </h3>
                                        </button>
                                    </div>

                                    {/* Contenu différent selon l'onglet mais sans sélection de cours */}
                                    {aiMode === 'existing' ? (
                                        <div className="space-y-4">
                                            <label className="block text-sm font-medium text-gray-700">
                                                Instructions pour l'examen
                                            </label>
                                            <ReactQuill
                                                value={courseContent}
                                                onChange={setCourseContent}
                                                modules={{
                                                    toolbar: [
                                                        ['bold', 'italic', 'underline', 'strike'],
                                                        ['blockquote'],
                                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                                                        [{ 'header': [1, 2, 3, false] }],
                                                        [{ 'color': [] }, { 'background': [] }],
                                                        [{ 'align': [] }],
                                                        ['clean']
                                                    ]
                                                }}
                                                style={{ height: '250px', marginBottom: '50px' }}
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
                                                    multiple
                                                    className="hidden"
                                                    id="course-upload"
                                                />
                                                <label
                                                    htmlFor="course-upload"
                                                    className="cursor-pointer flex flex-col items-center"
                                                >
                                                    <Upload size={32} className="text-gray-400 mb-3" />
                                                    <span className="text-gray-600 text-center">
                                                        Cliquez pour ajouter vos supports de cours en PDF
                                                    </span>
                                                    <span className="text-sm text-gray-500 mt-1">
                                                        Vous pouvez sélectionner plusieurs fichiers
                                                    </span>
                                                </label>
                                            </div>
                                            <div className="space-y-4">
                                                <label className="block text-sm font-medium text-gray-700">
                                                    Instructions supplémentaires
                                                </label>
                                                <ReactQuill
                                                    value={courseContent}
                                                    onChange={setCourseContent}
                                                    modules={{
                                                        toolbar: [
                                                            ['bold', 'italic', 'underline', 'strike'],
                                                            ['blockquote'],
                                                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                                                            [{ 'header': [1, 2, 3, false] }],
                                                            [{ 'color': [] }, { 'background': [] }],
                                                            [{ 'align': [] }],
                                                            ['clean']
                                                        ]
                                                    }}
                                                    style={{ height: '250px', marginBottom: '50px' }}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {!generatedFile ? (
                                        <div className='flex w-full items-center justify-center pt-5'>
                                            <button
                                                type="submit"
                                                className="w-80 bg-[#DC3545] text-white py-2 px-6 rounded-full hover:bg-[#C82333] transition-colors flex items-center justify-center gap-2"
                                                disabled={isGenerating}
                                            >
                                                {isGenerating ? (
                                                    <span>Génération en cours...</span>
                                                ) : (
                                                    <>
                                                        <Robot size={20} />
                                                        <span>Générer l'examen</span>
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="space-y-6">
                                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
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
                                                        {generatedFile
                                                            ? generatedFile.name
                                                            : "Cliquez pour modifier le PDF généré (OCR)"}
                                                    </span>
                                                    <span className="text-sm text-gray-500 mt-2">
                                                        Vous pouvez modifier le PDF généré grâce à notre système OCR
                                                    </span>
                                                </label>
                                            </div>
                                            <div className='flex w-full items-center justify-center pt-5'>
                                                <button
                                                    type="button"
                                                    onClick={handleSendToAdmin}
                                                    className="w-80 bg-[#28A745] text-white py-2 px-6 rounded-full hover:bg-[#299041] transition-colors flex items-center justify-center gap-2"
                                                >
                                                    <CheckCircle size={20} />
                                                    <span>Envoyer à l'administration</span>
                                                </button>
                                            </div>
                                        </div>
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