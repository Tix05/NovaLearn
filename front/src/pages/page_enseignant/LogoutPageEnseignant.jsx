import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';
import { teacherLogout } from '../../Services/teacherAuthService';
import Loading from '../Loading';

const LogoutPageEnseignant = () => {
    const navigate = useNavigate();

    useEffect(() => {
        teacherLogout();
        navigate('/enseignant/login-enseignant', { replace: true });
    }, [navigate]);

    return <Loading />;
};

export default LogoutPageEnseignant;