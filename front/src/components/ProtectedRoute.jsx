import { Navigate } from 'react-router-dom';

const ProtectedRoute = ({ children }) => {
    const user = JSON.parse(sessionStorage.getItem('user'));

    if (!user || !user.roles.includes('ROLE_ETUDIANT')) {
        return <Navigate to="/etudiant/login-etudiant" replace />;
    }

    return children;
};

export default ProtectedRoute;