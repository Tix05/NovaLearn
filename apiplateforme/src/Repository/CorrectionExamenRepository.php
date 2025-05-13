<?php

namespace App\Repository;

use App\Entity\CorrectionExamen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CorrectionExamen|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorrectionExamen|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorrectionExamen[]    findAll()
 * @method CorrectionExamen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorrectionExamenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorrectionExamen::class);
    }
}