<?php

namespace App\Repository;

use App\Entity\StatusRecord;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
     * Used to upgrade (rehash) the user_internal's password automatically over time.
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
        // 1. Get the Status ID (Since we are writing raw SQL, we need the ID, not the object)
        $activeStatusId = $this->getEntityManager()
            ->getRepository(StatusRecord::class)
            ->getActive()
            ->getId();

        // 2. Setup the Mapping (Tell Doctrine how to map the raw SQL result back to your User entity)
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(\App\Entity\User::class, 'u');

        // 3. Write the Raw SQL with the ::text cast
        // Note: 'app_user' is the table name in DB. Check if yours is 'user' or 'app_user'
        $sql = "
        SELECT u.* FROM app_user u
        WHERE u.status_id = :sts
        AND (u.roles::text LIKE :role_internal OR u.roles::text LIKE :role_admin)
    ";

        // 4. Create and Run the Query
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('sts', $activeStatusId);
        $query->setParameter('role_internal', '%"ROLE_INTERNAL"%');
        $query->setParameter('role_admin', '%"ROLE_ADMIN"%');

        return $query->getResult();
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
