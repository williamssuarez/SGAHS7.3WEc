<?php

namespace App\Repository;

use App\Entity\Condiciones;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Condiciones>
 */
class CondicionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Condiciones::class);
    }

    public function getActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->distinct()
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query;
    }

    public function getActivesforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query->getQuery()->getResult();
    }
}
