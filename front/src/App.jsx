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
import ProtectedRoute from './components/ProtectedRoute';
import ProtectedRouteEnseignant from './components/ProtectedRouteEnseignant';
import ProtectedRouteAdmin from './components/ProtectedRouteAdmin';
import LogoutPage from './pages/page_etudiant/LogoutPage';
import GenerateurDeDocument from './pages/page_admin/GenerateurDeDocument';
import LogoutPageEnseignant from './pages/page_enseignant/LogoutPageEnseignant';
import LogoutPageAdmin from './pages/page_admin/LogoutPageAdmin';

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
      <Route path="logout" element={<LogoutPage />} />
      <Route path="dashboard"
        element={
          <ProtectedRoute>
            <Enseignant />
          </ProtectedRoute>
        }
      />
      <Route path="bibliotheque"
        element={
          <ProtectedRoute>
            <Bibliotheque />
          </ProtectedRoute>
        } />
      <Route path="agenda"
        element={
          <ProtectedRoute>
            <Agenda />
          </ProtectedRoute>
        } />
      <Route path="message"
        element={
          <ProtectedRoute>
            <Message />
          </ProtectedRoute>
        } />
      <Route path="mention"
        element={
          <ProtectedRoute>
            <Mention />
          </ProtectedRoute>
        } />
      <Route path="cours/:mentionId"
        element={
          <ProtectedRoute>
            <Cours />
          </ProtectedRoute>
        } />
      <Route path="cours/:mentionId/:semestreId"
        element={
          <ProtectedRoute>
            <Cours />
          </ProtectedRoute>
        } />
      <Route path="cours/:mentionId/:semestreId/:coursId"
        element={
          <ProtectedRoute>
            <DescriptionCours />
          </ProtectedRoute>
        } />
    </Routes>
  );
}

function EnseignantRoutes() {
  return (
    <Routes>
      <Route path="logout-enseignant" element={<LogoutPageEnseignant />} />
      <Route path="login-enseignant" element={<LoginEnseignant />} />
      <Route path="forgot-password-teacher" element={<ForgotPasswordProf />} />
      <Route path="dashboard"
        element={
          <ProtectedRouteEnseignant>
            <Dashboard />
          </ProtectedRouteEnseignant>
        } />
      <Route path="etudiant"
        element={
          <ProtectedRouteEnseignant>
            <Etudiant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="mention"
        element={
          <ProtectedRouteEnseignant>
            <MentionEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="bibliotheque"
        element={
          <ProtectedRouteEnseignant>
            <BibliothequeEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="agenda"
        element={
          <ProtectedRouteEnseignant>
            <AgendaEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="message"
        element={
          <ProtectedRouteEnseignant>
            <MessageEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="examen/:mentionId/:semestreId/:coursId"
        element={
          <ProtectedRouteEnseignant>
            <Examen />
          </ProtectedRouteEnseignant>
        } />
      <Route path="coursEnseignant/:mentionId"
        element={
          <ProtectedRouteEnseignant>
            <CoursEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="coursEnseignant/:mentionId/:semestreId"
        element={
          <ProtectedRouteEnseignant>
            <CoursEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId"
        element={
          <ProtectedRouteEnseignant>
            <DescriptionCoursEnseignant />
          </ProtectedRouteEnseignant>
        } />
      <Route path="coursEnseignant/:mentionId/:semestreId/:coursId/ajouter-support"
        element={
          <ProtectedRouteEnseignant>
            <AjoutSupport />
          </ProtectedRouteEnseignant>
        } />
    </Routes>
  );
}

function AdminRoutes() {
  return (
    <Routes>
      <Route path="logout-admin" element={<LogoutPageAdmin />} />
      <Route path="login" element={<LoginAdmin />} />
      <Route path="dashboard"
        element={
          <ProtectedRouteAdmin>
            <DashboardAdmin />
          </ProtectedRouteAdmin>
        } />
      <Route path="forgot-password" element={<ForgotPasswordAdmin />} />
      <Route path="setting"
        element={
          <ProtectedRouteAdmin>
            <Setting />
          </ProtectedRouteAdmin>
        } />
      <Route path="user-management"
        element={
          <ProtectedRouteAdmin>
            <GestionUser />
          </ProtectedRouteAdmin>
        } />
      <Route path="exam-management"
        element={
          <ProtectedRouteAdmin>
            <GestionExamen />
          </ProtectedRouteAdmin >
        } />
      < Route path="bibliotheque"
        element={
          <ProtectedRouteAdmin>
            < BibliothequeAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="agenda"
        element={
          <ProtectedRouteAdmin>
            < AgendaAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="message"
        element={
          <ProtectedRouteAdmin>
            < MessageAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="pdf-generator"
        element={
          <ProtectedRouteAdmin>
            < GenerateurDeDocument />
          </ProtectedRouteAdmin>
        } />
      < Route path="mentions"
        element={
          <ProtectedRouteAdmin>
            < MentionAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="mentions/:mentionId/niveaux"
        element={
          <ProtectedRouteAdmin>
            < NiveauEtParcour />
          </ProtectedRouteAdmin>
        } />
      < Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours"
        element={
          <ProtectedRouteAdmin>
            < CoursAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours/:coursId"
        element={
          <ProtectedRouteAdmin>
            < DescriptionCoursAdmin />
          </ProtectedRouteAdmin>
        } />
      < Route path="mentions/:mentionId/niveaux/:niveauId/semestres/:semestreId/cours/:coursId/ajout-support"
        element={
          <ProtectedRouteAdmin>
            < AjoutSupportAdmin />
          </ProtectedRouteAdmin>
        } />
    </Routes >
  );
}

export default App;