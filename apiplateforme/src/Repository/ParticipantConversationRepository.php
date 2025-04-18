<?php

namespace App\Repository;

use App\Entity\ParticipantConversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParticipantConversation>
 *
 * @method ParticipantConversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipantConversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipantConversation[]    findAll()
 * @method ParticipantConversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantConversation::class);
    }

//    /**
//     * @return ParticipantConversation[] Returns an array of ParticipantConversation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ParticipantConversation
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
