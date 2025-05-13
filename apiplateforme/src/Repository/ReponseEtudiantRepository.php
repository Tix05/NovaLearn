<?php

namespace App\Repository;

use App\Entity\ReponseEtudiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReponseEtudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReponseEtudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReponseEtudiant[]    findAll()
 * @method ReponseEtudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReponseEtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReponseEtudiant::class);
    }
}