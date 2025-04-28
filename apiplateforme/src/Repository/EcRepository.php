<?php

namespace App\Repository;

use App\Entity\Ec;
use App\Entity\Mention;
use App\Entity\Parcours;
use App\Entity\Niveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ec::class);
    }

    public function add(Ec $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ec $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les ECs par mention, parcours et niveau
     * 
     * @param Mention $mention
     * @param Parcours $parcours
     * @param Niveau $niveau
     * @return Ec[]
     */
    public function findByMentionParcoursNiveau(Mention $mention, Parcours $parcours, Niveau $niveau): array
{
    return $this->createQueryBuilder('ec')
        ->join('ec.ue', 'ue')
        ->join('ue.mention', 'mention')
        ->leftJoin('App\Entity\UeParcours', 'ue_parcours', 'WITH', 'ue_parcours.ue = ue.id')
        ->join('ue_parcours.parcours', 'parcours')
        // Pas besoin de joindre niveau directement car il est déjà lié à parcours
        ->where('mention.id = :mentionId')
        ->andWhere('parcours.id = :parcoursId')
        ->andWhere('parcours.niveau = :niveauId') // Filtre via la relation parcours->niveau
        ->setParameter('mentionId', $mention->getId())
        ->setParameter('parcoursId', $parcours->getId())
        ->setParameter('niveauId', $niveau->getId())
        ->getQuery()
        ->getResult();
}
}