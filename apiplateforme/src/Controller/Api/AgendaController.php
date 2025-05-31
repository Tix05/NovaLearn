<?php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Service\AgendaService;
use Doctrine\ORM\EntityManagerInterface;
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
        private $entityManager;

        public function __construct(Security $security, AgendaService $agendaService, EntityManagerInterface $entityManager)
        {
            $this->security = $security;
            $this->agendaService = $agendaService;
            $this->entityManager = $entityManager;
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

    /**
     * @Route("/teacher", name="api_agenda_teacher", methods={"GET"})
     */
    public function getTeacherAgenda(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $prof = $this->entityManager->getRepository(Prof::class)
            ->findOneBy(['user' => $user]);

        if (!$prof) {
            return $this->json(['error' => 'Profil enseignant non trouvé'], 404);
        }

        $ecs = $prof->getEcs();
        $mentions = [];
        foreach ($ecs as $ec) {
            $ue = $ec->getUe();
            $mention = $ue->getMention();
            if ($mention && !in_array($mention, $mentions, true)) {
                $mentions[] = $mention;
            }
        }

        if (empty($mentions)) {
            return $this->json(['error' => 'Aucune mention associée à cet enseignant'], 404);
        }

        $mentionIds = array_map(function($mention) { return $mention->getId(); }, $mentions);

        $cours = $this->agendaService->getTeacherAgendaData($mentionIds, 'COURS');
        $evenements = $this->agendaService->getTeacherAgendaData($mentionIds, 'EVENEMENT');

        return $this->json([
            'cours' => $cours,
            'evenements' => $evenements
        ]);
    }
}