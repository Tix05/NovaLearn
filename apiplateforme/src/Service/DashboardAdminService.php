<?php

namespace App\Service;

use App\Entity\Etudiant;
use App\Entity\Prof;
use App\Entity\Ec;
use App\Entity\Mention;
use App\Entity\EtudiantExamenStatut;
use App\Entity\ConnectionLog;
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
                'prenom' => '',
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
                'prenom' => '',
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
            'adminCount' => 1,
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

        if (empty($mentions)) {
            $data['labels'] = ['Aucune donnée'];
            $data['datasets'][0]['data'] = [1];
            $data['datasets'][0]['backgroundColor'] = ['#D1D5DB'];
            $data['datasets'][0]['hoverBackgroundColor'] = ['#9CA3AF'];
            return $data;
        }

        $hasData = false;

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

            $presenceRate = $totalStudents > 0 ? ($presentStudents / $totalStudents) * 100 : 0;
            $data['datasets'][0]['data'][] = round($presenceRate, 2);

            if ($presenceRate > 0) {
                $hasData = true;
            }
        }

        if (!$hasData && array_sum($data['datasets'][0]['data']) === 0) {
            $data['labels'] = ['Aucune donnée'];
            $data['datasets'][0]['data'] = [1];
            $data['datasets'][0]['backgroundColor'] = ['#D1D5DB'];
            $data['datasets'][0]['hoverBackgroundColor'] = ['#9CA3AF'];
        }

        return $data;
    }

    private function getConnectionEvolutionData(array $mentions): array
    {
        $data = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Jui'],
            'datasets' => [],
        ];

        $currentYear = (new \DateTime())->format('Y');

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => array_fill(0, 7, 0),
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            try {
                $results = $this->entityManager->createQueryBuilder()
                    ->select('DISTINCT cl.id, cl.loginTime')
                    ->from(ConnectionLog::class, 'cl')
                    ->leftJoin(Etudiant::class, 'e', 'WITH', 'e.user = cl.user')
                    ->leftJoin('e.mention', 'm')
                    ->where('m.id = :mention OR m.id IS NULL')
                    ->andWhere('cl.loginTime >= :startDate')
                    ->andWhere('cl.loginTime < :endDate')
                    ->setParameter('mention', $mention->getId())
                    ->setParameter('startDate', new \DateTime("$currentYear-01-01"))
                    ->setParameter('endDate', new \DateTime(($currentYear + 1)."-01-01"))
                    ->getQuery()
                    ->getResult();

                foreach ($results as $result) {
                    $month = (int)$result['loginTime']->format('n') - 1;
                    if ($month >= 0 && $month < 7) {
                        $dataset['data'][$month]++;
                    }
                }
            } catch (\Exception $e) {
                $dataset['data'] = array_fill(0, 7, 0);
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

        $currentYear = (new \DateTime())->format('Y');

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => array_fill(0, 7, 0),
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            $results = $this->entityManager->getRepository(Etudiant::class)
                ->createQueryBuilder('e')
                ->select('e.id, e.created_at')
                ->where('e.mention = :mention')
                ->andWhere('e.status = :status')
                ->andWhere('e.created_at >= :startYear')
                ->andWhere('e.created_at < :endYear')
                ->setParameter('mention', $mention)
                ->setParameter('status', true)
                ->setParameter('startYear', new \DateTime("$currentYear-01-01"))
                ->setParameter('endYear', new \DateTime(($currentYear + 1)."-01-01"))
                ->getQuery()
                ->getResult();

            foreach ($results as $result) {
                $month = (int)$result['created_at']->format('n') - 1;
                if ($month >= 0 && $month < 7) {
                    $dataset['data'][$month]++;
                }
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

        $currentYear = (new \DateTime())->format('Y');

        foreach ($mentions as $index => $mention) {
            $dataset = [
                'label' => $mention->getName(),
                'data' => array_fill(0, 7, 0),
                'fill' => false,
                'borderColor' => ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6'][$index % 5],
                'tension' => 0.4,
            ];

            $results = $this->entityManager->getRepository(Prof::class)
                ->createQueryBuilder('p')
                ->select('DISTINCT p.id, p.created_at')
                ->join('p.ecs', 'ec')
                ->join('ec.ue', 'ue')
                ->where('ue.mention = :mention')
                ->andWhere('p.status = :status')
                ->andWhere('p.created_at >= :startYear')
                ->andWhere('p.created_at < :endYear')
                ->setParameter('mention', $mention)
                ->setParameter('status', true)
                ->setParameter('startYear', new \DateTime("$currentYear-01-01"))
                ->setParameter('endYear', new \DateTime(($currentYear + 1)."-01-01"))
                ->getQuery()
                ->getResult();

            foreach ($results as $result) {
                $month = (int)$result['created_at']->format('n') - 1;
                if ($month >= 0 && $month < 7) {
                    $dataset['data'][$month]++;
                }
            }

            $data['datasets'][] = $dataset;
        }

        return $data;
    }
}