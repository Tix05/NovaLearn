import React, { useState, useEffect } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link, useParams, useNavigate } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import AccordionUE from '../../components/AccordionUE';
import { getAdminCours, getAdminNiveaux } from '../../Services/adminAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

export default function CoursAdmin() {
    const { mentionId, niveauId, semestreId } = useParams();
    const navigate = useNavigate();
    const [data, setData] = useState(null);
    const [selectedSemestre, setSelectedSemestre] = useState(semestreId);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [niveauData, setNiveauData] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                // Récupérer les niveaux
                const niveaux = await getAdminNiveaux(mentionId);
                console.log('Réponse de getAdminNiveaux:', { mentionId, niveaux });

                // Normaliser niveauId en chaîne pour la comparaison
                const currentNiveau = niveaux.find(n => String(n.id) === String(niveauId));
                if (!currentNiveau) {
                    console.error('Niveau non trouvé pour niveauId:', niveauId, 'dans niveaux:', niveaux);
                    throw new Error(`Niveau non trouvé pour l'ID ${niveauId}`);
                }
                setNiveauData(currentNiveau);
                console.log('Niveau sélectionné:', currentNiveau);

                // Vérifier les semestres disponibles
                if (!currentNiveau.semestres?.length) {
                    throw new Error('Aucun semestre disponible pour ce niveau');
                }

                // Si aucun semestreId n'est spécifié, rediriger vers le premier semestre
                if (!semestreId) {
                    const firstSemestreId = currentNiveau.semestres[0].id;
                    console.log('Redirection vers le premier semestre:', firstSemestreId);
                    navigate(`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${firstSemestreId}/cours`, {
                        replace: true,
                    });
                    setSelectedSemestre(String(firstSemestreId));
                    return;
                }

                // Récupérer les cours
                const coursResponse = await getAdminCours(mentionId, niveauId, semestreId);
                console.log('Réponse de getAdminCours:', coursResponse);
                setData(coursResponse);
                setSelectedSemestre(String(semestreId));
                setLoading(false);
            } catch (err) {
                console.error('Erreur dans fetchData:', err);
                setError(err.message || 'Erreur lors du chargement des données');
                setLoading(false);
            }
        };
        fetchData();
    }, [mentionId, niveauId, semestreId, navigate]);

    if (loading) {
        return (
            <LayoutAdmin>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </LayoutAdmin>
        );
    }

    if (error) {
        return (
            <LayoutAdmin>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className="text-xl font-bold">Erreur lors du chargement des données</p>
                    <p className="text-lg font-semibold">{error}</p>
                </div>
            </LayoutAdmin>
        );
    }

    if (!niveauData?.semestres?.length) {
        return (
            <LayoutAdmin>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className="text-xl font-bold">Aucun semestre disponible</p>
                    <p className="text-lg font-semibold">Veuillez vérifier la configuration des semestres pour ce niveau.</p>
                </div>
            </LayoutAdmin>
        );
    }

    // Gestion des parcours
    const parcours = niveauData?.parcours?.length > 0
        ? niveauData.parcours.map(p => p.name).join(', ')
        : 'Non spécifié';

    const semestres = niveauData?.semestres || [];

    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl text-gray-800 font-semibold p-5'>{data?.mention.nom} - {data?.niveau.nom}</h1>
                <div className="flex items-center py-4 space-x-5 p-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${s.id}/cours`}
                            className={`pb-2 transition duration-400 ${String(selectedSemestre) === String(s.id)
                                ? "border-b-2 border-blue-500 text-blue-600 font-semibold"
                                : "text-gray-600 hover:text-blue-500"
                                }`}
                            onClick={() => setSelectedSemestre(String(s.id))}
                        >
                            {s.name}
                        </Link>
                    ))}
                </div>
                <Divider />
                <div className='flex justify-between items-center p-3'>
                    <h1 className='text-lg font-semibold text-gray-800'>Parcours : {parcours}</h1>
                </div>
            </div>
        );
    };

    return (
        <LayoutAdmin>
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                {renderHeader()}
                <AccordionUE
                    ues={data?.ues || []}
                    mentionId={mentionId}
                    niveauId={niveauId}
                    semestreId={selectedSemestre}
                />
            </div>
        </LayoutAdmin>
    );
}