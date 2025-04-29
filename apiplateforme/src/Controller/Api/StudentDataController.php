<?php


namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Repository\MentionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/student")
 */
class StudentDataController extends AbstractController
{
    private $security;
    private $mentionRepository;

    public function __construct(Security $security, MentionRepository $mentionRepository)
    {
        $this->security = $security;
        $this->mentionRepository = $mentionRepository;
    }

    /**
     * @Route("/mentions", name="api_student_mentions", methods={"GET"})
     */
    public function getStudentMentions(): Response
    {
        $user = $this->security->getUser();
        $etudiant = $user->getEtudiants()->first();
        
        if (!$etudiant) {
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], Response::HTTP_NOT_FOUND);
        }

        $mention = $etudiant->getMention();
        $parcours = $etudiant->getParcours();
        $niveau = $etudiant->getNiveau();

        // Construire la structure complète
        $mentionsData = $this->buildMentionStructure($mention, $parcours, $niveau, $etudiant);

        return $this->json($mentionsData);
    }

    private function buildMentionStructure($mention, $parcours, $niveau, $etudiant)
{
    $semestresData = [];
    
    foreach ($mention->getUes() as $ue) {
        if ($ue->getSemestre() && $ue->getSemestre()->getNiveau()->getId() === $niveau->getId()) {
            $semestreId = $ue->getSemestre()->getId();
            
            if (!isset($semestresData[$semestreId])) {
                $semestresData[$semestreId] = [
                    'id' => $semestreId,
                    'intitule' => $ue->getSemestre()->getName(),
                    'ues' => []
                ];
            }
            
            $ueData = [
                'id' => $ue->getId(),
                'nom' => $ue->getName(),
                'cours' => []
            ];
            
            foreach ($ue->getEcs() as $ec) {
                $ueData['cours'][] = [
                    'id' => $ec->getId(),
                    'titre' => $ec->getName(),
                    'credit' => $ec->getCoeff(),
                    'description' => $ec->getDescription() ?? 'Aucune description disponible',
                    'supports' => $this->getFormattedSupports($ec->getFichierSupports())
                ];
            }
            
            $semestresData[$semestreId]['ues'][] = $ueData;
        }
    }

    return [
        [
            'id' => $mention->getId(),
            'nom' => $mention->getName(),
            'niveau' => $niveau->getNom(),
            'parcours' => $parcours->getName(),
            'matricule' => $etudiant->getMatricule(),
            'icon' => $mention->getIcon(),
            'semestres' => array_values($semestresData)
        ]
    ];
}

private function getFormattedSupports($supports)
{
    $formatted = [];
    
    foreach ($supports as $support) {
        if (!$support->isEstPublique()) {
            continue; // Ne pas inclure les supports non publics
        }
        
        $formatted[] = [
            'id' => $support->getId(),
            'type' => $this->mapSupportType($support->getType()),
            'titre' => $support->getTitre(),
            'description' => $support->getDescription(),
            'url' => $support->getFichier() ? '/uploads/supports/' . $support->getFichier() : $support->getUrl(),
            'date_ajout' => $support->getDateAjout()->format('Y-m-d H:i:s')
        ];
    }
    
    return $formatted;
}

private function mapSupportType($type)
{
    $types = [
        FichierSupport::TYPE_FICHIER => 'document',
        FichierSupport::TYPE_VIDEO => 'video',
        FichierSupport::TYPE_AUDIO => 'audio',
        FichierSupport::TYPE_LIEN => 'lien'
    ];
    
    return $types[$type] ?? 'document';
}
}