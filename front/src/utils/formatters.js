export function formatDate(dateString) {
    if (!dateString) return '';

    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
}

export function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substring(2);
}