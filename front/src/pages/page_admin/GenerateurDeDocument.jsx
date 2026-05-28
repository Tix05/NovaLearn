import React, { useState, useEffect } from 'react';
import StudentForm from '../../components/componentsDocumentGenerating/StudentForm';
import DocumentPreview from '../../components/componentsDocumentGenerating/DocumentPreview';
import DocumentList from '../../components/componentsDocumentGenerating/DocumentList';
import DocumentSelector from '../../components/componentsDocumentGenerating/DocumentSelector';
import { generatePdf, downloadDocument } from '../../utils/pdfGenerator';
import LayoutAdmin from '../../components/LayoutAdmin';
import { FaAngleLeft } from "react-icons/fa";


const GenerateurDeDocument = () => {
    const [selectedType, setSelectedType] = useState(null);
    const [step, setStep] = useState('select');
    const [documents, setDocuments] = useState([]);
    const [currentDocument, setCurrentDocument] = useState(null);
    const [isGenerating, setIsGenerating] = useState(false);

    useEffect(() => {
        const savedDocs = localStorage.getItem('studentDocuments');
        if (savedDocs) {
            try {
                const parsedDocs = JSON.parse(savedDocs);
                // Ne pas essayer de restaurer pdfBlob car il n'est pas stocké
                const docsWithDates = parsedDocs.map(doc => ({
                    ...doc,
                    createdAt: new Date(doc.createdAt),
                    pdfBlob: null // Explicitement mettre à null
                }));
                setDocuments(docsWithDates);
            } catch (error) {
                console.error('Error parsing saved documents:', error);
            }
        }
    }, []);

    useEffect(() => {
        if (documents.length > 0) {
            // Ne stockez pas pdfBlob dans localStorage
            const docsToSave = documents.map(({ pdfBlob, ...rest }) => rest);
            localStorage.setItem('studentDocuments', JSON.stringify(docsToSave));
        }
    }, [documents]);

    const handleTypeSelect = (type) => {
        setSelectedType(type);
        setStep('form');
        setCurrentDocument(null);
    };

    const handleFormSubmit = async (studentInfo) => {
        if (!selectedType) return;

        setIsGenerating(true);

        try {
            const newDocument = await generatePdf(selectedType, studentInfo);
            setDocuments((prev) => [newDocument, ...prev]);
            setCurrentDocument(newDocument);
            setStep('preview');
        } catch (error) {
            console.error('Error generating document:', error);
            alert('Une erreur est survenue lors de la génération du document.');
        } finally {
            setIsGenerating(false);
        }
    };

    const handleDocumentSelect = (document) => {
        setCurrentDocument(document);
        setSelectedType(document.type);
        setStep('preview');
    };

    const handleDocumentDelete = (id) => {
        setDocuments((prev) => prev.filter((doc) => doc.id !== id));

        if (currentDocument && currentDocument.id === id) {
            setCurrentDocument(null);
            setStep('select');
        }

        const updatedDocs = documents.filter((doc) => doc.id !== id);
        const docsToSave = updatedDocs.map(({ pdfBlob, ...rest }) => rest);
        localStorage.setItem('studentDocuments', JSON.stringify(docsToSave));
    };

    const handleDocumentDownload = async (doc) => {  // Changé 'document' en 'doc'
        try {
            let docToDownload = doc;

            // Si pdfBlob est absent, régénérer le PDF
            if (!doc.pdfBlob) {
                docToDownload = await generatePdf(doc.type, doc.studentInfo);

                // Mettre à jour le document dans l'état
                setDocuments(prev =>
                    prev.map(d =>
                        d.id === doc.id ? docToDownload : d
                    )
                );
            }

            // Créer un blob à partir des données si nécessaire (sécurité)
            const blob = docToDownload.pdfBlob instanceof Blob
                ? docToDownload.pdfBlob
                : new Blob([docToDownload.pdfBlob], { type: 'application/pdf' });

            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a'); // Maintenant 'document' fait référence au DOM
            link.href = url;
            link.download = docToDownload.filename || `document_${docToDownload.id}.pdf`;
            document.body.appendChild(link);
            link.click();

            // Nettoyage
            setTimeout(() => {
                window.URL.revokeObjectURL(url);
                document.body.removeChild(link);
            }, 100);

            return true;
        } catch (error) {
            console.error('Erreur lors du téléchargement:', error);
            alert('Erreur lors du téléchargement du document.');
            return false;
        }
    };

    const handleBack = () => {
        if (step === 'preview') {
            setStep('form');
        } else if (step === 'form') {
            setStep('select');
            setSelectedType(null);
        }
    };

    return (
        <LayoutAdmin>
            <div
                className="bg-gray-50 custom-scrollbar overflow-y-auto"
                style={{ height: 'calc(100vh - 60px)' }}
            >
                <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                    <div className="flex flex-col gap-8">
                        <div className="w-full lg:w-2/3 space-y-6">
                            <nav className="flex items-center">
                                {step !== 'select' && (
                                    <button
                                        onClick={handleBack}
                                        className="flex text-sm text-white bg-emerald-700 px-5 rounded-lg py-2 hover:scale-105 duration-300 items-center justify-center"
                                    >
                                        <FaAngleLeft className="mr-2" size={20} />
                                        Retour
                                    </button>
                                )}
                            </nav>

                            <div className="bg-white p-6 rounded-lg shadow-sm min-h-[400px]">
                                {isGenerating ? (
                                    <div className="flex flex-col items-center justify-center h-64">
                                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600 mb-4"></div>
                                        <p className="text-gray-600">Génération du document en cours...</p>
                                    </div>
                                ) : (
                                    <>
                                        {step === 'select' && (
                                            <DocumentSelector
                                                selectedType={selectedType}
                                                onSelect={handleTypeSelect}
                                            />
                                        )}

                                        {step === 'form' && selectedType && (
                                            <StudentForm
                                                documentType={selectedType}
                                                onSubmit={handleFormSubmit}
                                            />
                                        )}

                                        {step === 'preview' && currentDocument && (
                                            <DocumentPreview
                                                document={currentDocument}
                                                onDownload={handleDocumentDownload}
                                                onDelete={handleDocumentDelete}
                                            />
                                        )}
                                    </>
                                )}
                            </div>
                        </div>

                        <div className="w-full lg:w-1/3">
                            <h2 className="text-xl font-bold text-gray-800">Mes documents</h2>
                            <DocumentList
                                documents={documents}
                                onSelect={handleDocumentSelect}
                                onDelete={handleDocumentDelete}
                                onDownload={handleDocumentDownload}
                            />
                        </div>
                    </div>
                </main>
            </div>
        </LayoutAdmin>
    );
};

export default GenerateurDeDocument;