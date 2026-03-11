<?php

namespace App\Repository;

use App\Entity\Prescripciones;
use App\Entity\StatusRecord;
use App\Enum\PrescripcionesEstados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prescripciones>
 */
class PrescripcionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prescripciones::class);
    }

    public function getActivesforTable($id)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,m,p')

            ->innerJoin('u.medicamento', 'm')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('m.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }

    public function getActivesforTableByState($id, PrescripcionesEstados $state)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,m,p')

            ->innerJoin('u.medicamento', 'm')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('m.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')
            ->andWhere('u.estado = :state')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
            ->setParameter('state', $state)
        ;

        return $query->getQuery()->getResult();
    }

    public function getActivesforTableByNotState($id, PrescripcionesEstados $state)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,m,p')

            ->innerJoin('u.medicamento', 'm')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('m.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')
            ->andWhere('u.estado != :state')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
            ->setParameter('state', $state)
        ;

        return $query->getQuery()->getResult();
    }
}
