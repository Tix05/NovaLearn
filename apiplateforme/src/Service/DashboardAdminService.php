<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Entity\Mention;
use App\Entity\EtudiantExamenStatut;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DashboardAdminService
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function getDashboardData(): array
    {
        $avatarBaseUrl = 'http://localhost:8000/uploads/avatars';

        // Récupérer les étudiants
        $students = $this->entityManager->getRepository(Etudiant::class)
            ->createQueryBuilder('e')
            ->select('e, u, m, p, n')
            ->join('e.user', 'u')
            ->join('e.mention', 'm')
            ->join('e.parcours', 'p')
            ->join('e.niveau', 'n')
            ->where('e.status = :status')
            ->setParameter('status', true)
            ->getQuery()
            ->getResult();

        $formattedStudents = array_map(function ($student) use ($avatarBaseUrl) {
            $avatar = $student->getUser()->getAvatar()
                ? $avatarBaseUrl . '/' . $student->getUser()->getAvatar()
                : 'https://www.gravatar.com/avatar/default?s=200&d=mm';

            return [
                'id' => $student->getId(),
                'photo' => $avatar,
                'nom' => $student->getUser()->getName(),
                'prenom' => '', // Ajustez si vous avez un champ prénom séparé
                'email' => $student->getUser()->getEmail(),
                'telephone' => $student->getUser()->getTelephone(),
                'niveau' => $student->getNiveau()->getNom(),
                'mention' => $student->getMention()->getName(),
                'parcours' => $student->getParcours()->getName(),
            ];
        }, $students);

        // Récupérer les enseignants
        $teachers = $this->entityManager->getRepository(Prof::class)
            ->createQueryBuilder('p')
            ->select('p, u')
            ->join('p.user', 'u')
            ->where('p.status = :status')
            ->setParameter('status', true)
            ->getQuery()
            ->getResult();

        $formattedTeachers = array_map(function ($teacher) use ($avatarBaseUrl) {
            $avatar = $teacher->getUser()->getAvatar()
                ? $avatarBaseUrl . '/' . $teacher->getUser()->getAvatar()
                : 'https://www.gravatar.com/avatar/default?s=200&d=mm';

            return [
                'id' => $teacher->getId(),
                'photo' => $avatar,
                'nom' => $teacher->getUser()->getName(),
                'prenom' => '', // Ajustez si vous avez un champ prénom séparé
                'email' => $teacher->getUser()->getEmail(),
                'telephone' => $teacher->getUser()->getTelephone(),
                'matiere' => $this->getTeacherMainSubject($teacher),
            ];
        }, $teachers);

        // Récupérer les mentions
        $mentions = $this->entityManager->getRepository(Mention::class)->findAll();

        // Statistiques
        $examPresenceData = $this->getExamPresenceData($mentions);
        $connectionEvolution = $this->getConnectionEvolutionData($mentions);
        $studentEvolution = $this->getStudentEvolutionData($mentions);
        $teacherEvolution = $this->getTeacherEvolutionData($mentions);

        return [
            'students' => $formattedStudents,
            'teachers' => $formattedTeachers,
            'adminCount' => 1, // Supposons un seul admin
            'examPresence' => $examPresenceData,
            'connectionEvolution' => $connectionEvolution,
            'studentEvolution' => $studentEvolution,
            'teacherEvolution' => $teacherEvolution,
        ];
    }

    private function getTeacherMainSubject(Prof $teacher): string
    {
        $ec = $this->entityManager->getRepository(Ec::class)
            ->findOneBy(['prof' => $teacher]);
        return $ec ? $ec->getName() : 'Non spécifié';
    }

    private function getExamPresenceData(array $mentions): array
    {
        $data = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => ['#3B82F6', '#F59E0B', '#10B981', '#EF4444', '#8B5CF6'],
                    'hoverBackgroundColor' => ['#2563EB', '#D97706', '#059669', '#DC2626', '#7C3AED'],
                ],
            ],
        ];

        foreach ($mentions as $mention) {
            $mentionId = $mention->getId();
            $data['labels'][] = $mention->getName();

            // Total des étudiants par mention
            $totalStudents = $this->entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.mention = :mention')
                ->andWhere('e.status = :status')
                ->setParameter('mention', $mentionId)
                ->setParameter('status', true)
                ->getQuery()
                ->getSingleScalarResult();

            // Étudiants présents aux examens
            $presentStudents = $this->entityManager->getRepository(EtudiantExamenStatut::class)
                ->createQueryBuilder('ees')
                ->select('COUNT(DISTINCT ees.etudiant)')
                ->join('ees.etudiant', 'e')
                ->where('e.mention = :mention')
                ->andWhere('ees.statut = :statut')
                ->setParameter('mention', $mentionId)
                ->setParameter('statut', 'present')
                ->getQuery()
                ->getSingleScalarResult();

            // Calculer le taux de présence
            $presenceRate = $totalStudents > 0 ? ($presentStudents / $totalStudents) * 100 : 0;
            $data['datasets'][0]['data'][] = round($presenceRate, 2);
        }

        return $data;
    }

    private function getConnectionEvolutionData(array $mentions): array
    {
        $data = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui'],
            'datasets' => [],
        ];

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => [],
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            // Compter les connexions par mois pour les étudiants de cette mention
            for ($month = 1; $month <= 7; $month++) {
                $count = $this->entityManager->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->select('COUNT(DISTINCT u.id)')
                    ->join('u.etudiants', 'e')
                    ->where('e.mention = :mention')
                    ->andWhere('u.online_statut = :statut')
                    ->andWhere('MONTH(u.updated_at) = :month')
                    ->setParameter('mention', $mention->getId())
                    ->setParameter('statut', 'online')
                    ->setParameter('month', $month)
                    ->getQuery()
                    ->getSingleScalarResult();

                $dataset['data'][] = (int)$count;
            }

            $data['datasets'][] = $dataset;
        }

        return $data;
    }

    private function getStudentEvolutionData(array $mentions): array
    {
        $data = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui'],
            'datasets' => [],
        ];

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => [],
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            // Compter les inscriptions d'étudiants par mois
            for ($month = 1; $month <= 7; $month++) {
                $count = $this->entityManager->getRepository(Etudiant::class)
                    ->createQueryBuilder('e')
                    ->select('COUNT(e.id)')
                    ->where('e.mention = :mention')
                    ->andWhere('e.status = :status')
                    ->andWhere('MONTH(e.created_at) = :month')
                    ->setParameter('mention', $mention->getId())
                    ->setParameter('status', true)
                    ->setParameter('month', $month)
                    ->getQuery()
                    ->getSingleScalarResult();

                $dataset['data'][] = (int)$count;
            }

            $data['datasets'][] = $dataset;
        }

        return $data;
    }

    private function getTeacherEvolutionData(array $mentions): array
    {
        $data = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui'],
            'datasets' => [],
        ];

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => [],
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            // Compter les enseignants par mois (en fonction des ECs associés à la mention)
            for ($month = 1; $month <= 7; $month++) {
                $count = $this->entityManager->getRepository(Prof::class)
                    ->createQueryBuilder('p')
                    ->select('COUNT(DISTINCT p.id)')
                    ->join('p.ecs', 'ec')
                    ->join('ec.ue', 'ue')
                    ->where('ue.mention = :mention')
                    ->andWhere('p.status = :status')
                    ->andWhere('MONTH(p.created_at) = :month')
                    ->setParameter('mention', $mention->getId())
                    ->setParameter('status', true)
                    ->setParameter('month', $month)
                    ->getQuery()
                    ->getSingleScalarResult();

                $dataset['data'][] = (int)$count;
            }

            $data['datasets'][] = $dataset;
        }

        return $data;
    }
}