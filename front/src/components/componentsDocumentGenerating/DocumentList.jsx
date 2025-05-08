import React from 'react';
import { FileText, CreditCard, Award, FileSpreadsheet, Trash, Download } from 'lucide-react';

const DocumentList = ({ documents, onSelect, onDelete, onDownload }) => {
    if (documents.length === 0) {
        return (
            <div className="text-center p-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <p className="text-gray-500">Aucun document généré</p>
            </div>
        );
    }

    const getIcon = (type) => {
        switch (type) {
            case 'certificate': return <FileText className="w-8 h-8" />;
            case 'card': return <CreditCard className="w-8 h-8" />;
            case 'attestation': return <Award className="w-8 h-8" />;
            case 'transcript': return <FileSpreadsheet className="w-8 h-8" />;
            default: return <FileText className="w-8 h-8" />;
        }
    };

    const getDocumentLabel = (type) => {
        switch (type) {
            case 'certificate': return 'Certificat de Scolarité';
            case 'card': return 'Carte Étudiant';
            case 'attestation': return 'Attestation de Réussite';
            case 'transcript': return 'Relevé de Notes';
            default: return 'Document';
        }
    };

    return (
        <div className="bg-white rounded-lg border border-gray-200 pb-10">
            <h3 className="px-4 py-3 border-b border-gray-200 font-medium text-gray-700">
                Documents générés ({documents.length})
            </h3>
            <ul className="divide-y divide-gray-200">
                {documents.map((doc) => (
                    <li
                        key={doc.id}
                        className="p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                    >
                        <div className="flex items-center justify-between">
                            <div
                                className="flex items-center space-x-3 flex-grow"
                                onClick={() => onSelect(doc)}
                            >
                                <div className={`
                  p-2 rounded-full items-center justify-center flex
                  ${doc.type === 'certificate' ? 'bg-green-100 text-green-600' : ''}
                  ${doc.type === 'card' ? 'bg-green-100 text-green-600' : ''}
                  ${doc.type === 'attestation' ? 'bg-green-100 text-green-600' : ''}
                  ${doc.type === 'transcript' ? 'bg-green-100 text-green-600' : ''}
                `}>
                                    {getIcon(doc.type)}
                                </div>
                                <div>
                                    <p className="font-medium text-gray-800">
                                        {getDocumentLabel(doc.type)}
                                    </p>
                                    <p className="text-sm text-gray-500">
                                        {doc.studentInfo.firstName} {doc.studentInfo.lastName} - {new Date(doc.createdAt).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-center space-x-2">
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDownload(doc);
                                    }}
                                    className="p-2 text-gray-600 hover:text-green-700 transition-colors"
                                    title="Télécharger"
                                >
                                    <Download className="w-8 h-8" />
                                </button>
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDelete(doc.id);
                                    }}
                                    className="p-2 text-red-500 hover:text-red-700 transition-colors"
                                    title="Supprimer"
                                >
                                    <Trash className="w-8 h-8" />
                                </button>
                            </div>
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default DocumentList;