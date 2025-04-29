<?php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Repository\EcRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/ecs")
 */
class EcController extends AbstractController
{
    private $ecRepository;
    private $security;

    public function __construct(EcRepository $ecRepository, Security $security)
    {
        $this->ecRepository = $ecRepository;
        $this->security = $security;
    }

    /**
     * @Route("/profs_by_etudiant", name="api_ec_profs_by_etudiant", methods={"GET"})
     */
    public function getProfsByEtudiant(): JsonResponse
    {
        /** @var Etudiant $etudiant */
        $etudiant = $this->security->getUser()->getEtudiants()->first();
        
        if (!$etudiant) {
            return $this->json(['error' => 'Étudiant non trouvé'], 404);
        }

        $mention = $etudiant->getMention();
        $parcours = $etudiant->getParcours();
        $niveau = $etudiant->getNiveau();

        $ecs = $this->ecRepository->findByMentionParcoursNiveau(
            $mention,
            $parcours,
            $niveau
        );

        $profs = [];
        $uniqueProfs = [];

        foreach ($ecs as $ec) {
            $prof = $ec->getProf();
            $profId = $prof->getId();
        
            if (!isset($uniqueProfs[$profId])) {
                $uniqueProfs[$profId] = true;
                $profs[] = [
                    'id' => $prof->getId(),
                    'user' => [
                        'nomComplet' => $prof->getUser()->getName(), // Utilisation de getName()
                        'avatar' => $prof->getUser()->getAvatar()
                    ],
                    'ecs' => [], // Initialisation du tableau
                    'niveau' => $niveau->getNom()
                ];
            }
        
            // Ajouter l'EC au professeur
            foreach ($profs as &$p) {
                if ($p['id'] === $profId) {
                    $p['ecs'][] = $ec->getName(); // Correction ici
                    break;
                }
            }
        }

        return $this->json($profs);
    }
}