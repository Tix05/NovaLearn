<?php

namespace App\Repository;

use App\Entity\Agenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Agenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agenda[]    findAll()
 * @method Agenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    public function findByTypeAndFilters(?string $type, ?int $mentionId, ?int $parcoursId, ?int $niveauId): array
{
    $qb = $this->createQueryBuilder('a')
        ->andWhere('a.date_expiration >= :now')
        ->setParameter('now', new \DateTimeImmutable());

    if ($type) {
        $qb->andWhere('a.type = :type')
           ->setParameter('type', $type);
    }

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

    return $qb->orderBy('a.date', 'ASC')
              ->getQuery()
              ->getResult();
}
}