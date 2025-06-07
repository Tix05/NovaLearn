import { Navigate } from 'react-router-dom';

const ProtectedRouteEnseignant = ({ children }) => {
    const user = JSON.parse(localStorage.getItem('admin'));

    if (!user || !user.roles.includes('ROLE_ADMIN')) {
        return <Navigate to="/admin/login" replace />;
    }

    return children;
};

export default ProtectedRouteEnseignant;