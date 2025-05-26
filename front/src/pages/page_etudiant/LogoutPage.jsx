import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';

const LogoutPage = () => {
    const navigate = useNavigate();

    const logout = () => {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        navigate('/etudiant/login-etudiant', { replace: true });
    };
    useEffect(() => {
        logout();
    }, []);

    return (
        <div>

        </div>
    );
};

export default LogoutPage;