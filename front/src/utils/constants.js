export const DOCUMENT_TYPES = {
    CERTIFICATE: 'certificate',
    CARD: 'card',
    ATTESTATION: 'attestation',
    TRANSCRIPT: 'transcript',
};

export const getDocumentTypeName = (type) => {
    switch (type) {
        case DOCUMENT_TYPES.CERTIFICATE: return 'Certificat de Scolarité';
        case DOCUMENT_TYPES.CARD: return 'Carte Étudiant';
        case DOCUMENT_TYPES.ATTESTATION: return 'Attestation de Réussite';
        case DOCUMENT_TYPES.TRANSCRIPT: return 'Relevé de Notes';
        default: return 'Document';
    }
};