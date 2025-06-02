import { useNavigate } from 'react-router-dom';
import { useEffect } from 'react';
import Loading from '../Loading';
import { adminLogout } from '../../Services/adminAuthService';

const LogoutPageAdmin = () => {
    const navigate = useNavigate();

    useEffect(() => {
        adminLogout().then(() => {
            navigate('/admin/login', { replace: true });
        });
    }, [navigate]);

    return <Loading />;
};

export default LogoutPageAdmin;