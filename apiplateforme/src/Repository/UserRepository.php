<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Parcours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function findAllExcept(int $userId): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.id != :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findByParcours(Parcours $parcours): array
{
    return $this->createQueryBuilder('u')
        ->join('u.etudiants', 'e')
        ->where('e.parcours = :parcours')
        ->setParameter('parcours', $parcours)
        ->getQuery()
        ->getResult();
}

    // Dans UserRepository.php
public function findByRole(string $role, ?int $excludeUserId = null): array
{
    $qb = $this->createQueryBuilder('u')
        ->where('u.roles LIKE :role')
        ->setParameter('role', '%"' . $role . '"%');

    if ($excludeUserId !== null) {
        $qb->andWhere('u.id != :excludeId')
           ->setParameter('excludeId', $excludeUserId);
    }

    return $qb->getQuery()->getResult();
}
}