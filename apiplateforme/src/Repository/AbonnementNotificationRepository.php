<?php

namespace App\Repository;

use App\Entity\AbonnementNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbonnementNotification>
 *
 * @method AbonnementNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbonnementNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbonnementNotification[]    findAll()
 * @method AbonnementNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbonnementNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbonnementNotification::class);
    }

//    /**
//     * @return AbonnementNotification[] Returns an array of AbonnementNotification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AbonnementNotification
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
