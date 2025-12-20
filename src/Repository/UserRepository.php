<?php

namespace App\Repository;

use App\Entity\StatusRecord;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function getActivesInternalsforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.roles LIKE :role_internal') //get internal only
            ->andWhere('u.roles NOT LIKE :role_external') //exclude external

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('role_external', '%"ROLE_EXTERNAL"%')
            ->setParameter('role_internal', '%"ROLE_INTERNAL"%')
        ;

        return $query->getQuery()->getResult();
    }

    public function getActivesExternalsforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.roles LIKE :role_external') //get external only
            ->andWhere('u.roles NOT LIKE :role_internal') //exclude internal

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('role_external', '%"ROLE_EXTERNAL"%')
            ->setParameter('role_internal', '%"ROLE_INTERNAL"%')
        ;

        return $query->getQuery()->getResult();
    }
}
