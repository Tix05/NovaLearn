<?php

namespace App\Repository;

use App\Entity\NotificationGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationGroupe>
 *
 * @method NotificationGroupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationGroupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationGroupe[]    findAll()
 * @method NotificationGroupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationGroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationGroupe::class);
    }

//    /**
//     * @return NotificationGroupe[] Returns an array of NotificationGroupe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NotificationGroupe
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
