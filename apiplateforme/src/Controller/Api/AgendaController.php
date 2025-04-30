<?php

// src/Controller/Api/AgendaController.php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Service\AgendaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/agenda")
 */
class AgendaController extends AbstractController
{
    private $security;
    private $agendaService;

    public function __construct(Security $security, AgendaService $agendaService)
    {
        $this->security = $security;
        $this->agendaService = $agendaService;
    }

    /**
     * @Route("/student", name="api_agenda_student", methods={"GET"})
     */
    public function getStudentAgenda(): JsonResponse
    {
        $user = $this->security->getUser();
        $etudiant = $user->getEtudiants()->first();

        if (!$etudiant) {
            return $this->json(['message' => 'Aucune donnée étudiante trouvée'], 404);
        }

        $agendaData = $this->agendaService->getStudentAgendaData($etudiant);

        return $this->json($agendaData);
    }
}