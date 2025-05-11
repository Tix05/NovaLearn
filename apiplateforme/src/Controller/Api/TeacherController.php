<?php

namespace App\Controller\Api;

use App\Entity\Prof;
use App\Entity\Etudiant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class TeacherController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/api/teacher/students", name="api_teacher_students", methods={"GET"})
     */
    public function getStudentsByTeacher(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        /** @var Prof $prof */
        $prof = $this->entityManager->getRepository(Prof::class)
            ->findOneBy(['user' => $user]);

        if (!$prof) {
            return $this->json(['error' => 'Profil enseignant non trouvé'], 404);
        }

        // Récupérer les ECs de l'enseignant
        $ecs = $prof->getEcs();

        // Récupérer les mentions associées aux ECs via les UEs
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

        // Récupérer les étudiants liés à ces mentions
        $students = $this->entityManager->getRepository(Etudiant::class)
            ->createQueryBuilder('e')
            ->select('e, u, m, p, n')
            ->join('e.user', 'u')
            ->join('e.mention', 'm')
            ->join('e.parcours', 'p')
            ->join('e.niveau', 'n')
            ->where('e.mention IN (:mentions)')
            ->andWhere('e.status = :status')
            ->setParameter('mentions', $mentions)
            ->setParameter('status', true)
            ->getQuery()
            ->getResult();

        // Base URL pour les avatars
        $avatarBaseUrl = $this->getParameter('app.base_url') . '/uploads/avatars';

        // Formater les données
        $formattedStudents = array_map(function ($student) use ($avatarBaseUrl) {
            $avatar = $student->getUser()->getAvatar()
                ? $avatarBaseUrl . '/' . $student->getUser()->getAvatar()
                : 'https://www.gravatar.com/avatar/default?s=200&d=mm';

            return [
                'id' => $student->getId(),
                'numero' => $student->getId(), // Utiliser l'ID comme numéro (à ajuster si vous avez un champ spécifique)
                'nom' => $student->getUser()->getName(),
                'email' => $student->getUser()->getEmail(),
                'mention' => $student->getMention()->getName(),
                'niveau' => $student->getNiveau()->getNom(),
                'profil' => $avatar,
            ];
        }, $students);

        return $this->json($formattedStudents);
    }
}