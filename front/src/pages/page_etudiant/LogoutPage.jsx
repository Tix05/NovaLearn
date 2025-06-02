import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';
import { logout } from '../../Services/authService';

const LogoutPage = () => {
    const navigate = useNavigate();

    useEffect(() => {
        logout().then(() => {
            navigate('/etudiant/login-etudiant', { replace: true });
        });
    }, [navigate]);

    return <div></div>;
};

export default LogoutPage;