import { useState, useEffect } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Acceuil from './pages/Acceuil';
import LoginEnseignant from './pages/page_enseignant/LoginEnseignant';
import LoginEtudiant from './pages/page_etudiant/LoginEtudiant';
import ForgotPassword from './pages/page_etudiant/ForgotPassword';
import ForgotPasswordProf from './pages/page_enseignant/ForgotPasswordProf';
import Loading from './pages/Loading';
import Inscription from './pages/page_etudiant/Inscription';
import Inscription2 from './pages/page_etudiant/Inscription2';
import { AnimatePresence } from 'framer-motion';
import Enseignant from './pages/page_etudiant/Enseignant';

function App() {
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 1500);
    return () => clearTimeout(timer);
  }, []);

  if (isLoading) {
    return <Loading />;
  }

  return (
    <AnimatePresence>
      <body className='min-h-screen'>
        <Routes>
          <Route path="/" element={<Acceuil />} />
          <Route path="/etudiant/*" element={<EtudiantRoutes />} />
          <Route path="/enseignant/*" element={<EnseignantRoutes />} />
          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </body>
    </AnimatePresence>
  );
}

function EtudiantRoutes() {
  return (
    <Routes>
      <Route path="login-etudiant" element={<LoginEtudiant />} />
      <Route path="forgot-password" element={<ForgotPassword />} />
      <Route path="information" element={<Inscription />} />
      <Route path="registration" element={<Inscription2 />} />
      <Route path="teacher" element={<Enseignant />} />
    </Routes>
  );
}

function EnseignantRoutes() {
  return (
    <Routes>
      <Route path="login-enseignant" element={<LoginEnseignant />} />
      <Route path="forgot-password-teacher" element={<ForgotPasswordProf />} />
    </Routes>
  );
}

export default App;