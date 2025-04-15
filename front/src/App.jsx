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
import MessageEnseignant from './pages/page_enseignant/MessageEnseignant';
import LoginAdmin from './pages/page_admin/LoginAdmin';
import ForgotPasswordAdmin from './pages/page_admin/ForgotPasswordAdmin';
import DashboardAdmin from './pages/page_admin/DashboardAdmin';
import Setting from './pages/page_admin/Setting';
import MentionAdmin from './pages/page_admin/MentionAdmin';
import NiveauEtParcour from './pages/page_admin/NiveauEtParcour';
import CoursAdmin from './pages/page_admin/CoursAdmin';
import DescriptionCoursAdmin from './pages/page_admin/DescriptionCoursAdmin';
import GestionUser from './pages/page_admin/GestionUser';
import BibliothequeAdmin from './pages/page_admin/BibliothequeAdmin';
import AgendaAdmin from './pages/page_admin/AgendaAdmin';
import MessageAdmin from './pages/page_admin/MessageAdmin';
import GestionExamen from './pages/page_admin/GestionExamen';
import Examen from './pages/page_enseignant/Examen';
import AjoutSupportAdmin from './pages/page_admin/AjoutSupportAdmin';

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
          <Route path="/admin/*" element={<AdminRoutes />} />
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
      <Route path="message" element={<MessageEnseignant />} />
      <Route path="examen" element={<Examen />} />
      <Route path="coursEnseignant/:mentionId" element={<CoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId" element={<CoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId" element={<DescriptionCoursEnseignant />} />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId/ajouter-support" element={<AjoutSupport />} />
    </Routes>
  );
}

function AdminRoutes() {
  return (
    <Routes>
      <Route path="login" element={<LoginAdmin />} />
      <Route path="dashboard" element={<DashboardAdmin />} />
      <Route path="forgot-password" element={<ForgotPasswordAdmin />} />
      <Route path="setting" element={<Setting />} />
      <Route path="user-management" element={<GestionUser />} />
      <Route path="exam-management" element={<GestionExamen />} />
      <Route path="bibliotheque" element={<BibliothequeAdmin />} />
      <Route path="agenda" element={<AgendaAdmin />} />
      <Route path="message" element={<MessageAdmin />} />
      <Route path="mentions" element={<MentionAdmin />} />
      <Route path="mentions/:mentionId/niveaux" element={<NiveauEtParcour />} />
      <Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours" element={<CoursAdmin />} />
      <Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours/:coursId" element={<DescriptionCoursAdmin />} />
      <Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours/:coursId/ajout-support" element={<AjoutSupportAdmin />} />
    </Routes>
  );
}

export default App;