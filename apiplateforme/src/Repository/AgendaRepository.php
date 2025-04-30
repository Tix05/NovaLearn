<?php

namespace App\Repository;

use App\Entity\Agenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    public function findForStudent($mentionId, $parcoursId, $niveauId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('(a.type = :coursType OR a.type = :examenType)')
            ->andWhere('(a.mention = :mentionId AND a.parcours = :parcoursId AND a.niveau = :niveauId)')
            ->orWhere('a.type = :eventType')
            ->setParameter('coursType', Agenda::TYPE_COURS)
            ->setParameter('examenType', Agenda::TYPE_EXAMEN)
            ->setParameter('eventType', Agenda::TYPE_EVENEMENT)
            ->setParameter('mentionId', $mentionId)
            ->setParameter('parcoursId', $parcoursId)
            ->setParameter('niveauId', $niveauId)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTypeAndFilters($type, $mentionId = null, $parcoursId = null, $niveauId = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.type = :type')
            ->setParameter('type', $type);

        if ($type !== Agenda::TYPE_EVENEMENT) {
            if ($mentionId) {
                $qb->andWhere('a.mention = :mentionId')
                   ->setParameter('mentionId', $mentionId);
            }
            if ($parcoursId) {
                $qb->andWhere('a.parcours = :parcoursId')
                   ->setParameter('parcoursId', $parcoursId);
            }
            if ($niveauId) {
                $qb->andWhere('a.niveau = :niveauId')
                   ->setParameter('niveauId', $niveauId);
            }
        }

        return $qb->orderBy('a.date', 'ASC')
                 ->getQuery()
                 ->getResult();
    }
    public function add(Agenda $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Agenda $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Agenda[] Returns an array of Agenda objects
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

//    public function findOneBySomeField($value): ?Agenda
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
