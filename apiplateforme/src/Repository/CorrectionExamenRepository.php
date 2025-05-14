<?php

namespace App\Repository;

use App\Entity\CorrectionExamen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CorrectionExamen>
 *
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

    /**
     * Find corrections by exam ID.
     *
     * @param int $examenId
     * @return CorrectionExamen[]
     */
    public function findByExamen(int $examenId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.examen = :examenId')
            ->setParameter('examenId', $examenId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find corrections by student ID.
     *
     * @param int $etudiantId
     * @return CorrectionExamen[]
     */
    public function findByEtudiant(int $etudiantId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.etudiant = :etudiantId')
            ->setParameter('etudiantId', $etudiantId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find corrections by exam and student.
     *
     * @param int $examenId
     * @param int $etudiantId
     * @return CorrectionExamen|null
     */
    public function findOneByExamenAndEtudiant(int $examenId, int $etudiantId): ?CorrectionExamen
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.examen = :examenId')
            ->andWhere('c.etudiant = :etudiantId')
            ->setParameter('examenId', $examenId)
            ->setParameter('etudiantId', $etudiantId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}