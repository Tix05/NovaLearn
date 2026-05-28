import React from 'react';
import { FileText, CreditCard, Award, FileSpreadsheet } from 'lucide-react';

const DocumentSelector = ({ selectedType, onSelect }) => {
    const documentTypes = [
        {
            id: 'certificate',
            label: 'Certificat de Scolarité',
            description: 'Document officiel attestant de votre inscription',
            icon: <FileText className="w-10 h-10" />
        },
        {
            id: 'card',
            label: 'Carte Étudiant',
            description: 'Carte d\'identification de l\'étudiant',
            icon: <CreditCard className="w-10 h-10" />
        },
        {
            id: 'attestation',
            label: 'Attestation de Réussite',
            description: 'Document certifiant la réussite d\'un programme',
            icon: <Award className="w-10 h-10" />
        },
        {
            id: 'transcript',
            label: 'Relevé de Notes',
            description: 'Document détaillant vos résultats académiques',
            icon: <FileSpreadsheet className="w-10 h-10" />
        }
    ];

    return (
        <div className="w-full max-w-5xl mx-auto">
            <h2 className="text-2xl font-bold text-gray-800 mb-6">
                Sélectionnez le type de document à générer
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 items-center justify-center">
                {documentTypes.map((doc) => (
                    <button
                        key={doc.id}
                        onClick={() => onSelect(doc.id)}
                        className={`
              flex items-start p-6 rounded-lg border-2 transition-all duration-300 w-[30vw]
              ${selectedType === doc.id
                                ? 'border-emerald-600 bg-emerald-50'
                                : 'border-gray-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/50'}
            `}
                    >
                        <div className={`
              flex-shrink-0 p-3 rounded-lg mr-4
              ${selectedType === doc.id
                                ? 'text-emerald-600 bg-emerald-100'
                                : 'text-gray-500 bg-gray-100'}
            `}>
                            {doc.icon}
                        </div>
                        <div className="text-left">
                            <h3 className={`
                font-semibold text-lg mb-1
                ${selectedType === doc.id ? 'text-emerald-700' : 'text-gray-800'}
              `}>
                                {doc.label}
                            </h3>
                            <p className="text-gray-600 text-sm">{doc.description}</p>
                        </div>
                    </button>
                ))}
            </div>
        </div>
    );
};

export default DocumentSelector;