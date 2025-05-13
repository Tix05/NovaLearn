<?php

namespace App\Repository;

use App\Entity\OptionQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OptionQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionQuestion[]    findAll()
 * @method OptionQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionQuestion::class);
    }
}