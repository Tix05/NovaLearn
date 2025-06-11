import { Navigate } from 'react-router-dom';

const ProtectedRouteEnseignant = ({ children }) => {
    const user = JSON.parse(sessionStorage.getItem('teacher'));

    if (!user || !user.roles.includes('ROLE_PROFESSEUR')) {
        return <Navigate to="/enseignant/login-enseignant" replace />;
    }

    return children;
};

export default ProtectedRouteEnseignant;