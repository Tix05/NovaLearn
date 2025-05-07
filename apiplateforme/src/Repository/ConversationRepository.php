<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Find conversations by participant (user)
     *
     * @param User $user
     * @return Conversation[]
     */
    public function findByParticipant(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'pc')
            ->where('pc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}