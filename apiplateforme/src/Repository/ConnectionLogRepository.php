<?php

namespace App\Repository;

use App\Entity\ConnectionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConnectionLog>
 *
 * @method ConnectionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConnectionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConnectionLog[]    findAll()
 * @method ConnectionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConnectionLog::class);
    }

    /**
     * Récupère les connexions des utilisateurs distincts par mention pour une période donnée.
     *
     * @param int $mentionId ID de la mention
     * @param \DateTimeInterface $startDate Date de début
     * @param \DateTimeInterface $endDate Date de fin
     * @return array Liste des connexions avec user_id et login_time
     */
    public function findConnectionsByMention(int $mentionId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('cl')
            ->select('DISTINCT cl.user, cl.loginTime')
            ->join('App\Entity\Etudiant', 'e', 'WITH', 'e.user = cl.user')
            ->where('e.mention = :mention')
            ->andWhere('cl.loginTime >= :startDate')
            ->andWhere('cl.loginTime < :endDate')
            ->setParameter('mention', $mentionId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de connexions pour un utilisateur donné.
     *
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de connexions
     */
    public function countConnectionsByUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('cl')
            ->select('COUNT(cl.id)')
            ->where('cl.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}