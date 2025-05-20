<?php

namespace App\Repository;

use App\Entity\EtudiantExamenStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtudiantExamenStatut|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtudiantExamenStatut|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtudiantExamenStatut[]    findAll()
 * @method EtudiantExamenStatut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantExamenStatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtudiantExamenStatut::class);
    }

    // Vous pouvez ajouter ici vos méthodes personnalisées pour le repository
}