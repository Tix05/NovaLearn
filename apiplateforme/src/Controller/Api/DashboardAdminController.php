<?php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Service\DashboardAdminService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DashboardAdminController extends AbstractController
{
    private $entityManager;
    private $security;
    private $dashboardService;

    public function __construct(EntityManagerInterface $entityManager, Security $security, DashboardAdminService $dashboardService)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->dashboardService = $dashboardService;
    }

    /**
     * @Route("/api/admin/dashboard", name="api_admin_dashboard", methods={"GET"})
     */
    public function getDashboardData(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Accès non autorisé'], 403);
        }

        try {
            // Récupérer les données via le service
            $data = $this->dashboardService->getDashboardData();

            return $this->json($data, 200, [], ['groups' => ['dashboard:read']]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la récupération des données : ' . $e->getMessage()], 500);
        }
    }
}