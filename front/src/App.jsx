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
import Mention from './pages/page_etudiant/Mention';
import Bibliotheque from './pages/page_etudiant/Bibliotheque';
import Agenda from './pages/page_etudiant/Agenda';
import Message from './pages/page_etudiant/Message';
import Cours from './pages/page_etudiant/Cours';
import DescriptionCours from './pages/page_etudiant/DescriptionCours';
import Dashboard from './pages/page_enseignant/Dashboard';
import Etudiant from './pages/page_enseignant/Etudiant';
import MentionEnseignant from './pages/page_enseignant/MentionEnseignant';
import CoursEnseignant from './pages/page_enseignant/CoursEnseignant';
import DescriptionCoursEnseignant from './pages/page_enseignant/DescriptionCoursEnseignant';
import AjoutSupport from './pages/page_enseignant/AjoutSupport';
import BibliothequeEnseignant from './pages/page_enseignant/BibliothequeEnseignant';
import AgendaEnseignant from './pages/page_enseignant/AgendaEnseignant';

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
      <Route path="inscription-etape-1" element={<Inscription />} />
      <Route path="inscription-etape-2" element={<Inscription2 />} />
      <Route path="enseignant" element={<Enseignant />} />
      <Route path="bibliotheque" element={<Bibliotheque />} />
      <Route path="agenda" element={<Agenda />} />
      <Route path="message" element={<Message />} />
      <Route path="mention" element={<Mention />} />
      <Route path="cours/:mentionId" element={<Cours />} />
      <Route path="cours/:mentionId/:semestreId" element={<Cours />} />
      <Route path="cours/:mentionId/:semestreId/:coursId" element={<DescriptionCours />} />
    </Routes>
  );
}

function EnseignantRoutes() {
  return (
    <Routes>
      <Route path="login-enseignant" element={<LoginEnseignant />} />
      <Route path="forgot-password-teacher" element={<ForgotPasswordProf />} />
      <Route path="dashboard" element={<Dashboard />} />
      <Route path="etudiant" element={<Etudiant />} />
      <Route path="mention" element={<MentionEnseignant />} />
      <Route path="bibliotheque" element={<BibliothequeEnseignant />} />
      <Route path="agenda" element={<AgendaEnseignant />} />
      <Route path="coursEnseignant/:mentionId" element={<CoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId" element={<CoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId" element={<DescriptionCoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId/ajouter-support" element={<AjoutSupport />} />
    </Routes>
  );
}

export default App;