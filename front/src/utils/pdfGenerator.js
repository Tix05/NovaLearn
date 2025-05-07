import { jsPDF } from 'jspdf';
import { formatDate, generateId } from './formatters';
import { getDocumentTypeName } from './constants';

export function generatePdf(type, studentInfo) {
    return new Promise((resolve) => {
        const doc = new jsPDF();
        const newDocument = {
            id: generateId(),
            type,
            createdAt: new Date(),
            filename: generateFilename(type, studentInfo),
            studentInfo,
        };

        generatePdfContent(doc, type, studentInfo);
        newDocument.pdfBlob = doc.output('blob');
        resolve(newDocument);
    });
}

function generatePdfContent(doc, type, studentInfo) {
    doc.setFontSize(20);
    doc.setTextColor(34, 197, 94); // text-green-500

    const title = getDocumentTypeName(type);
    const titleWidth = doc.getTextWidth(title);
    const pageWidth = doc.internal.pageSize.width;
    doc.text(title, (pageWidth - titleWidth) / 2, 20);

    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);

    let yPos = 40;
    const lineHeight = 10;

    doc.text(`Nom: ${studentInfo.lastName}`, 20, yPos);
    yPos += lineHeight;
    doc.text(`Prénom: ${studentInfo.firstName}`, 20, yPos);
    yPos += lineHeight;
    doc.text(`N° Étudiant: ${studentInfo.studentId}`, 20, yPos);
    yPos += lineHeight;
    doc.text(`Programme: ${studentInfo.program}`, 20, yPos);
    yPos += lineHeight;
    doc.text(`Année Académique: ${studentInfo.academicYear}`, 20, yPos);
    yPos += lineHeight;

    switch (type) {
        case 'transcript':
            if (studentInfo.grades && studentInfo.grades.length > 0) {
                yPos += lineHeight;
                doc.text('Relevé de Notes:', 20, yPos);
                yPos += lineHeight;

                studentInfo.grades.forEach(grade => {
                    doc.text(`${grade.courseId} - ${grade.courseName}: ${grade.grade} (${grade.credits} crédits)`, 20, yPos);
                    yPos += lineHeight;
                });

                yPos += lineHeight;
                const totalCredits = studentInfo.grades.reduce((sum, grade) => sum + grade.credits, 0);
                const averageGrade = (studentInfo.grades.reduce((sum, grade) =>
                    sum + (typeof grade.grade === 'number' ? grade.grade : 0), 0) /
                    (studentInfo.grades.length || 1)).toFixed(2);

                doc.text(`Moyenne générale: ${averageGrade}`, 20, yPos);
                yPos += lineHeight;
                doc.text(`Total des crédits: ${totalCredits}`, 20, yPos);
            }
            break;

        case 'attestation':
            if (studentInfo.graduationDate) {
                yPos += lineHeight;
                doc.text(`Date d'obtention: ${new Date(studentInfo.graduationDate).toLocaleDateString('fr-FR')}`, 20, yPos);
            }
            break;

        case 'card':
            if (studentInfo.dateOfBirth) {
                yPos += lineHeight;
                doc.text(`Date de naissance: ${new Date(studentInfo.dateOfBirth).toLocaleDateString('fr-FR')}`, 20, yPos);
            }
            break;
    }

    const footer = `Document généré le ${new Date().toLocaleDateString('fr-FR')}`;
    const footerWidth = doc.getTextWidth(footer);
    doc.text(footer, (pageWidth - footerWidth) / 2, doc.internal.pageSize.height - 10);
}

function generateFilename(type, studentInfo) {
    const dateStr = new Date().toISOString().split('T')[0];
    const studentName = `${studentInfo.lastName}_${studentInfo.firstName}`.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, '_');

    switch (type) {
        case 'certificate':
            return `certificat_scolarite_${studentName}_${dateStr}.pdf`;
        case 'card':
            return `carte_etudiant_${studentName}_${dateStr}.pdf`;
        case 'attestation':
            return `attestation_reussite_${studentName}_${dateStr}.pdf`;
        case 'transcript':
            return `releve_notes_${studentName}_${dateStr}.pdf`;
        default:
            return `document_${studentName}_${dateStr}.pdf`;
    }
}

export function downloadDocument(document) {
    if (!document.pdfBlob) {
        console.error('No PDF blob available for download');
        return false;
    }

    try {
        const blob = document.pdfBlob instanceof Blob
            ? document.pdfBlob
            : new Blob([document.pdfBlob], { type: 'application/pdf' });

        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = document.filename || `document_${document.id}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => {
            URL.revokeObjectURL(url);
        }, 100);

        return true;
    } catch (error) {
        console.error('Download error:', error);
        return false;
    }
}