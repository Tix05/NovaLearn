import React, { useEffect, useState } from 'react';

const StudentForm = ({ documentType, onSubmit }) => {
    const [studentInfo, setStudentInfo] = useState({
        firstName: '',
        lastName: '',
        studentId: '',
        program: '',
        academicYear: '',
        dateOfBirth: '',
        enrollmentDate: '',
        graduationDate: '',
        grades: []
    });

    const [grades, setGrades] = useState([
        { courseName: '', courseId: '', grade: '', credits: 0 }
    ]);

    useEffect(() => {
        setStudentInfo({
            firstName: '',
            lastName: '',
            studentId: '',
            program: '',
            academicYear: '',
            dateOfBirth: '',
            enrollmentDate: '',
            graduationDate: '',
            grades: []
        });
        setGrades([{ courseName: '', courseId: '', grade: '', credits: 0 }]);
    }, [documentType]);

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setStudentInfo({ ...studentInfo, [name]: value });
    };

    const handleGradeChange = (index, field, value) => {
        const updatedGrades = [...grades];
        updatedGrades[index] = { ...updatedGrades[index], [field]: value };
        setGrades(updatedGrades);
    };

    const addGradeRow = () => {
        setGrades([...grades, { courseName: '', courseId: '', grade: '', credits: 0 }]);
    };

    const removeGradeRow = (index) => {
        if (grades.length > 1) {
            const updatedGrades = grades.filter((_, i) => i !== index);
            setGrades(updatedGrades);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const formData = { ...studentInfo };

        if (documentType === 'transcript') {
            formData.grades = grades;
        }

        onSubmit(formData);
    };

    const inputClassName = "w-full px-3 py-2 border border-green-500 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500";

    const renderFormFields = () => {
        const commonFields = (
            <>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-center justify-center">
                    <div>
                        <label htmlFor="firstName" className="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                        <input
                            id="firstName"
                            name="firstName"
                            type="text"
                            required
                            value={studentInfo.firstName}
                            onChange={handleInputChange}
                            className={inputClassName}
                        />
                    </div>
                    <div>
                        <label htmlFor="lastName" className="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                        <input
                            id="lastName"
                            name="lastName"
                            type="text"
                            required
                            value={studentInfo.lastName}
                            onChange={handleInputChange}
                            className={inputClassName}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-center justify-center">
                    <div>
                        <label htmlFor="studentId" className="block text-sm font-medium text-gray-700 mb-1">Numéro d'étudiant</label>
                        <input
                            id="studentId"
                            name="studentId"
                            type="text"
                            required
                            value={studentInfo.studentId}
                            onChange={handleInputChange}
                            className={inputClassName}
                        />
                    </div>
                    <div>
                        <label htmlFor="program" className="block text-sm font-medium text-gray-700 mb-1">Programme d'études</label>
                        <input
                            id="program"
                            name="program"
                            type="text"
                            required
                            value={studentInfo.program}
                            onChange={handleInputChange}
                            className={inputClassName}
                        />
                    </div>
                </div>

                <div className="mb-4">
                    <label htmlFor="academicYear" className="block text-sm font-medium text-gray-700 mb-1">Année académique</label>
                    <input
                        id="academicYear"
                        name="academicYear"
                        type="text"
                        required
                        value={studentInfo.academicYear}
                        onChange={handleInputChange}
                        className={inputClassName}
                        placeholder="ex: 2024-2025"
                    />
                </div>
            </>
        );

        switch (documentType) {
            case 'certificate':
                return (
                    <>
                        {commonFields}
                        <div className="mb-4 items-center justify-center">
                            <label htmlFor="enrollmentDate" className="block text-sm font-medium text-gray-700 mb-1">Date d'inscription</label>
                            <input
                                id="enrollmentDate"
                                name="enrollmentDate"
                                type="date"
                                value={studentInfo.enrollmentDate || ''}
                                onChange={handleInputChange}
                                className={inputClassName}
                            />
                        </div>
                    </>
                );

            case 'card':
                return (
                    <>
                        {commonFields}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-center justify-center">
                            <div>
                                <label htmlFor="dateOfBirth" className="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                                <input
                                    id="dateOfBirth"
                                    name="dateOfBirth"
                                    type="date"
                                    required
                                    value={studentInfo.dateOfBirth || ''}
                                    onChange={handleInputChange}
                                    className={inputClassName}
                                />
                            </div>
                            <div>
                                <label htmlFor="enrollmentDate" className="block text-sm font-medium text-gray-700 mb-1">Date d'inscription</label>
                                <input
                                    id="enrollmentDate"
                                    name="enrollmentDate"
                                    type="date"
                                    required
                                    value={studentInfo.enrollmentDate || ''}
                                    onChange={handleInputChange}
                                    className={inputClassName}
                                />
                            </div>
                        </div>
                    </>
                );

            case 'attestation':
                return (
                    <>
                        {commonFields}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 items-center justify-center">
                            <div>
                                <label htmlFor="enrollmentDate" className="block text-sm font-medium text-gray-700 mb-1">Date d'inscription</label>
                                <input
                                    id="enrollmentDate"
                                    name="enrollmentDate"
                                    type="date"
                                    required
                                    value={studentInfo.enrollmentDate || ''}
                                    onChange={handleInputChange}
                                    className={inputClassName}
                                />
                            </div>
                            <div>
                                <label htmlFor="graduationDate" className="block text-sm font-medium text-gray-700 mb-1">Date d'obtention</label>
                                <input
                                    id="graduationDate"
                                    name="graduationDate"
                                    type="date"
                                    required
                                    value={studentInfo.graduationDate || ''}
                                    onChange={handleInputChange}
                                    className={inputClassName}
                                />
                            </div>
                        </div>
                    </>
                );

            case 'transcript':
                return (
                    <>
                        {commonFields}
                        <div className="mb-6">
                            <div className="flex justify-between items-center mb-3">
                                <h3 className="text-lg font-medium text-gray-800">Notes des cours</h3>
                                <button
                                    type="button"
                                    onClick={addGradeRow}
                                    className="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium"
                                >
                                    + Ajouter un cours
                                </button>
                            </div>

                            {grades.map((grade, index) => (
                                <div key={index} className="grid grid-cols-5 gap-2 mb-3 items-center justify-center">
                                    <div className="col-span-2">
                                        <input
                                            type="text"
                                            placeholder="Nom du cours"
                                            value={grade.courseName}
                                            onChange={(e) => handleGradeChange(index, 'courseName', e.target.value)}
                                            className={inputClassName}
                                        />
                                    </div>
                                    <div>
                                        <input
                                            type="text"
                                            placeholder="Code"
                                            value={grade.courseId}
                                            onChange={(e) => handleGradeChange(index, 'courseId', e.target.value)}
                                            className={inputClassName}
                                        />
                                    </div>
                                    <div>
                                        <input
                                            type="text"
                                            placeholder="Note"
                                            value={grade.grade}
                                            onChange={(e) => handleGradeChange(index, 'grade', e.target.value)}
                                            className={inputClassName}
                                        />
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <input
                                            type="number"
                                            placeholder="Crédits"
                                            value={grade.credits || ''}
                                            onChange={(e) => handleGradeChange(index, 'credits', Number(e.target.value))}
                                            className={inputClassName}
                                            min="0"
                                        />
                                        {grades.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeGradeRow(index)}
                                                className="p-2 text-red-500 hover:text-red-700"
                                                aria-label="Supprimer"
                                            >
                                                ✕
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                );

            default:
                return commonFields;
        }
    };

    return (
        <div className="w-full mx-auto bg-white p-6 rounded-lg shadow-sm">
            <h2 className="text-xl font-bold text-gray-800 mb-6">
                Informations pour {getDocumentTypeLabel(documentType)}
            </h2>

            <form onSubmit={handleSubmit}>
                {renderFormFields()}

                <div className="mt-6 flex justify-end">
                    <button
                        type="submit"
                        className="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Générer le document
                    </button>
                </div>
            </form>
        </div>
    );
};

function getDocumentTypeLabel(type) {
    switch (type) {
        case 'certificate': return 'Certificat de Scolarité';
        case 'card': return 'Carte Étudiant';
        case 'attestation': return 'Attestation de Réussite';
        case 'transcript': return 'Relevé de Notes';
        default: return '';
    }
}

export default StudentForm;