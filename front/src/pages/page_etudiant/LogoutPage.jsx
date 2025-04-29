import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';

const LogoutPage = () => {
    const navigate = useNavigate();

    const logout = () => {
        // Suppression de tous les éléments liés à l'authentification
        localStorage.removeItem('user');
        localStorage.removeItem('token');

        // Redirection vers la page de login
        navigate('/etudiant/login-etudiant', { replace: true });

        // Optionnel : Rechargement pour nettoyer complètement l'état
        window.location.reload();
    };

    // Déconnexion automatique au montage du composant
    useEffect(() => {
        logout();
    }, []);

    return (
        <div>

        </div>
    );
};

export default LogoutPage;