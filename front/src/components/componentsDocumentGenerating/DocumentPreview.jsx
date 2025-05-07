import React from 'react';
import { formatDate } from '../../utils/formatters';

const DocumentPreview = ({ document, onDownload, onDelete }) => {
    const renderDocumentContent = () => {
        const { type, studentInfo } = document;

        switch (type) {
            case 'certificate':
                return renderCertificate(studentInfo);
            case 'card':
                return renderStudentCard(studentInfo);
            case 'attestation':
                return renderAttestation(studentInfo);
            case 'transcript':
                return renderTranscript(studentInfo);
            default:
                return <p>Type de document non reconnu</p>;
        }
    };

    return (
        <div className="w-full">
            <div className="mb-4 flex justify-between items-center">
                <h2 className="text-xl font-bold text-gray-800">
                    Aperçu du document
                </h2>
                <div className="flex gap-2">
                    <button
                        onClick={() => onDownload(document)}  // Ici 'document' est la prop passée au composant
                        className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors flex items-center gap-1"
                    >
                        Télécharger
                    </button>
                    <button
                        onClick={() => onDelete(document.id)}
                        className="px-4 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors flex items-center gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>

            <div className="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-md">
                {renderDocumentContent()}
            </div>
        </div>
    );
};

const renderCertificate = (studentInfo) => (
    <div className="p-8 max-w-full relative">
        <div className="absolute top-0 left-0 w-full h-16 bg-green-600"></div>
        <div className="relative pt-12 text-center">
            <h1 className="text-2xl font-bold text-green-800 mb-6">CERTIFICAT DE SCOLARITÉ</h1>
            <p className="text-lg mb-8">L'Université certifie que</p>
            <p className="text-xl font-semibold mb-6">{studentInfo.firstName} {studentInfo.lastName}</p>
            <p className="mb-6">Numéro d'étudiant: <span className="font-semibold">{studentInfo.studentId}</span></p>
            <p className="mb-6">Est inscrit(e) pour l'année académique <span className="font-semibold">{studentInfo.academicYear}</span></p>
            <p className="mb-6">Dans le programme: <span className="font-semibold">{studentInfo.program}</span></p>
            {studentInfo.enrollmentDate && (
                <p className="mb-10">Date d'inscription: <span className="font-semibold">{formatDate(studentInfo.enrollmentDate)}</span></p>
            )}
            <div className="mt-16 flex justify-between items-center">
                <div>
                    <p className="text-sm">Date d'émission</p>
                    <p className="font-semibold">{formatDate(new Date().toISOString())}</p>
                </div>
                <div>
                    <p className="text-sm">Signature</p>
                    <div className="h-10 w-40 border-b border-gray-400"></div>
                </div>
            </div>
        </div>
    </div>
);

const renderStudentCard = (studentInfo) => (
    <div className="p-0">
        <div className="w-full aspect-[85/54] bg-gradient-to-r from-green-600 to-green-700 rounded-lg overflow-hidden relative flex flex-col justify-between p-6 text-white">
            <div className="flex justify-between items-start">
                <div>
                    <h2 className="text-xl font-bold mb-1">CARTE ÉTUDIANT</h2>
                    <p className="text-green-100 text-sm">Université Exemple</p>
                </div>
                <div className="bg-white text-green-800 rounded-full h-16 w-16 flex items-center justify-center font-bold">
                    LOGO
                </div>
            </div>

            <div className="flex mt-4">
                <div className="w-24 h-32 bg-gray-200 rounded-md flex items-center justify-center text-gray-400 mr-4">
                    PHOTO
                </div>
                <div>
                    <p className="text-sm text-green-100">Nom & Prénom</p>
                    <p className="font-semibold mb-2">{studentInfo.firstName} {studentInfo.lastName}</p>

                    <p className="text-sm text-green-100">N° Étudiant</p>
                    <p className="font-semibold mb-2">{studentInfo.studentId}</p>

                    <p className="text-sm text-green-100">Formation</p>
                    <p className="font-semibold mb-2">{studentInfo.program}</p>

                    {studentInfo.dateOfBirth && (
                        <>
                            <p className="text-sm text-green-100">Date de naissance</p>
                            <p className="font-semibold">{formatDate(studentInfo.dateOfBirth)}</p>
                        </>
                    )}
                </div>
            </div>

            <div className="mt-4 flex justify-between items-end">
                <div>
                    <p className="text-sm text-green-100">Année Académique</p>
                    <p className="font-semibold">{studentInfo.academicYear}</p>
                </div>
                <div className="text-right">
                    <p className="text-sm text-green-100">Valide jusqu'au</p>
                    <p className="font-semibold">31/08/{studentInfo.academicYear.split('-')[1]}</p>
                </div>
            </div>
        </div>
    </div>
);

const renderAttestation = (studentInfo) => (
    <div className="p-8 max-w-full relative">
        <div className="absolute top-0 left-0 w-full h-16 bg-green-600"></div>
        <div className="relative pt-12 text-center">
            <h1 className="text-2xl font-bold text-green-800 mb-6">ATTESTATION DE RÉUSSITE</h1>

            <p className="text-lg mb-8">
                L'Université atteste que
            </p>

            <p className="text-xl font-semibold mb-6">
                {studentInfo.firstName} {studentInfo.lastName}
            </p>

            <p className="mb-6">
                Numéro d'étudiant: <span className="font-semibold">{studentInfo.studentId}</span>
            </p>

            <p className="mb-6">
                A validé avec succès le programme <span className="font-semibold">{studentInfo.program}</span>
            </p>

            <p className="mb-6">
                Pour l'année académique <span className="font-semibold">{studentInfo.academicYear}</span>
            </p>

            {studentInfo.graduationDate && (
                <p className="mb-10">
                    Date d'obtention: <span className="font-semibold">{formatDate(studentInfo.graduationDate)}</span>
                </p>
            )}

            <div className="mt-16 flex justify-between items-center">
                <div>
                    <p className="text-sm">Date d'émission</p>
                    <p className="font-semibold">{formatDate(new Date().toISOString())}</p>
                </div>
                <div>
                    <p className="text-sm">Cachet et signature</p>
                    <div className="h-20 w-40 border border-dashed border-gray-400 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>
);

const renderTranscript = (studentInfo) => (
    <div className="p-8 max-w-full">
        <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-gray-800 mb-2">RELEVÉ DE NOTES</h1>
            <p className="text-gray-600">Année Académique {studentInfo.academicYear}</p>
        </div>

        <div className="mb-8">
            <h2 className="text-lg font-semibold border-b border-gray-300 pb-2 mb-4">Informations de l'étudiant</h2>
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <p className="text-sm text-gray-600">Nom & Prénom</p>
                    <p className="font-medium">{studentInfo.firstName} {studentInfo.lastName}</p>
                </div>
                <div>
                    <p className="text-sm text-gray-600">Numéro d'étudiant</p>
                    <p className="font-medium">{studentInfo.studentId}</p>
                </div>
                <div>
                    <p className="text-sm text-gray-600">Programme</p>
                    <p className="font-medium">{studentInfo.program}</p>
                </div>
            </div>
        </div>

        <div>
            <h2 className="text-lg font-semibold border-b border-gray-300 pb-2 mb-4">Résultats</h2>

            <div className="overflow-x-auto">
                <table className="min-w-full border-collapse">
                    <thead>
                        <tr className="bg-gray-100">
                            <th className="border px-4 py-2 text-left">Code</th>
                            <th className="border px-4 py-2 text-left">Cours</th>
                            <th className="border px-4 py-2 text-center">Note</th>
                            <th className="border px-4 py-2 text-center">Crédits</th>
                        </tr>
                    </thead>
                    <tbody>
                        {studentInfo.grades && studentInfo.grades.length > 0 ? (
                            studentInfo.grades.map((grade, index) => (
                                <tr key={index} className={index % 2 === 0 ? 'bg-gray-50' : ''}>
                                    <td className="border px-4 py-2">{grade.courseId}</td>
                                    <td className="border px-4 py-2">{grade.courseName}</td>
                                    <td className="border px-4 py-2 text-center">{grade.grade}</td>
                                    <td className="border px-4 py-2 text-center">{grade.credits}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={4} className="border px-4 py-2 text-center text-gray-500">
                                    Aucune note disponible
                                </td>
                            </tr>
                        )}
                    </tbody>
                    {studentInfo.grades && studentInfo.grades.length > 0 && (
                        <tfoot>
                            <tr className="bg-gray-100 font-semibold">
                                <td colSpan={2} className="border px-4 py-2 text-right">
                                    Total
                                </td>
                                <td className="border px-4 py-2 text-center">
                                    {(studentInfo.grades.reduce((sum, grade) =>
                                        sum + (typeof grade.grade === 'number' ? grade.grade : 0), 0) /
                                        (studentInfo.grades.length || 1)).toFixed(2)}
                                </td>
                                <td className="border px-4 py-2 text-center">
                                    {studentInfo.grades.reduce((sum, grade) => sum + grade.credits, 0)}
                                </td>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            <div className="mt-8 text-right">
                <p className="text-sm text-gray-600">Date d'émission</p>
                <p className="font-medium">{formatDate(new Date().toISOString())}</p>
            </div>
        </div>
    </div>
);

export default DocumentPreview;