import axios from 'axios';

const getCurrentUserData = () => {
    const admin = localStorage.getItem('admin');
    if (admin) return { type: 'admin', data: JSON.parse(admin), storageKey: 'admin' };

    const user = localStorage.getItem('user');
    if (user) return { type: 'user', data: JSON.parse(user), storageKey: 'user' };

    const teacher = localStorage.getItem('teacher');
    if (teacher) return { type: 'teacher', data: JSON.parse(teacher), storageKey: 'teacher' };

    return null;
};

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            const userData = getCurrentUserData();

            if (userData) {
                localStorage.removeItem(userData.storageKey);

                let redirectPath = '/login';
                let errorMessage = 'Session expirée, veuillez vous reconnecter';

                if (userData.type === 'admin') {
                    redirectPath = '/admin/login';
                    errorMessage = 'Session administrateur expirée, veuillez vous reconnecter';
                } else if (userData.type === 'teacher') {
                    redirectPath = '/enseignant/login-enseignant';
                    errorMessage = 'Session enseignant expirée, veuillez vous reconnecter';
                } else if (userData.type === 'user') {
                    redirectPath = '/etudiant/login-etudiant';
                    errorMessage = 'Session étudiant expirée, veuillez vous reconnecter';
                }

                window.location.href = redirectPath;
                return Promise.reject(new Error(errorMessage));
            }

            window.location.href = '/etudiant/login-etudiant';
            return Promise.reject(new Error('Session non autorisée, veuillez vous reconnecter'));
        }

        return Promise.reject(error);
    }
);

export default axios;