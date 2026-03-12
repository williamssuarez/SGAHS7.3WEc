<?php

namespace App\Repository;

use App\Entity\Citas;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Citas>
 */
class CitasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Citas::class);
    }

    public function getActivesforTableByState($state, $from, $to)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.estadoCita = :state')
            ->andWhere('u.fecha between :from AND :to')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('state', $state)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }
}
