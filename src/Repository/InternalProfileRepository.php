<?php

namespace App\Repository;

use App\Entity\InternalProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InternalProfile>
 */
class InternalProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InternalProfile::class);
    }

    public function getUserByValueforCheck($field, $value, $id = null, $extraField = null, $extraValue = null)
    {
        $qb = $this->createQueryBuilder('u');
        $whereField = 'u.' . $field . ' = :value';

        $query = $qb
            ->select('u')

            ->where($whereField);

        if ($extraField) {
            $extra = 'u.' . $extraField . ' = :extra';
            $qb->andWhere($extra)
                ->setParameter('extra', $extraValue);
        }

        if ($id){
            $qb->andWhere('u.id != :profileId')
                ->setParameter('profileId', $id);
        }

        $qb->setParameter('value', $value)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }
}
