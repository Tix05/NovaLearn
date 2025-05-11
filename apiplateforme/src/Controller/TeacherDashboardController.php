<?php

namespace App\Controller;

use App\Entity\Prof;
use App\Entity\Etudiant;
use App\Entity\Mention;
use App\Entity\FichierSupport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TeacherDashboardController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/teacher/dashboard/stats", name="api_teacher_dashboard_stats", methods={"GET"})
     */
    public function getDashboardStats(UserInterface $user): Response
    {
        // Récupérer l'enseignant connecté
        $prof = $this->entityManager->getRepository(Prof::class)
            ->findOneBy(['user' => $user]);

        if (!$prof) {
            return $this->json([
                'message' => 'Aucun profil enseignant trouvé pour cet utilisateur',
            ], Response::HTTP_FORBIDDEN);
        }

        // Récupérer les ECs de l'enseignant
        $ecs = $prof->getEcs();
        $ecIds = array_map(fn($ec) => $ec->getId(), $ecs->toArray());

        // Récupérer les mentions associées aux ECs via les UEs
        $mentions = [];
        foreach ($ecs as $ec) {
            $ue = $ec->getUe();
            $mention = $ue->getMention();
            if ($mention && !in_array($mention, $mentions, true)) {
                $mentions[] = $mention;
            }
        }

        // Compter les étudiants et fichiers par mention
        $stats = [];
        foreach ($mentions as $mention) {
            // Nombre d'étudiants
            $studentCount = $this->entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.mention = :mention')
                ->andWhere('e.status = :status')
                ->setParameter('mention', $mention)
                ->setParameter('status', true)
                ->getQuery()
                ->getSingleScalarResult();

            // Nombre de fichiers soumis dans les ECs de cette mention
            $fileCount = $this->entityManager->getRepository(FichierSupport::class)
                ->createQueryBuilder('f')
                ->select('COUNT(f.id)')
                ->join('f.ec', 'ec')
                ->join('ec.ue', 'ue')
                ->where('ue.mention = :mention')
                ->andWhere('ec.id IN (:ecIds)')
                ->setParameter('mention', $mention)
                ->setParameter('ecIds', $ecIds)
                ->getQuery()
                ->getSingleScalarResult();

            $stats[] = [
                'mention_id' => $mention->getId(),
                'mention_name' => $mention->getName(),
                'student_count' => (int) $studentCount,
                'file_count' => (int) $fileCount,
            ];
        }

        return $this->json([
            'mentions_count' => count($mentions),
            'mentions' => $stats,
        ]);
    }
}