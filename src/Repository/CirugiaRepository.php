<?php

namespace App\Repository;

use App\Entity\Cirugia;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cirugia>
 */
class CirugiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cirugia::class);
    }

    public function findDailySchedule(\DateTime $today)
    {
        $from = clone $today->setTime(0, 0, 0);
        $to = clone $today->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.fechaHoraProgramada between :from and :to')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }

    public function getHistoricalCirugiasByDate(\DateTime $start, \DateTime $end, \App\Entity\StatusRecord $status, ?\App\Enum\CirugiaEstados $estado = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c', 'p', 'q', 'm') // Eager load
            ->join('c.paciente', 'p')
            ->leftJoin('c.quirofano', 'q')
            ->leftJoin('c.cirujanoPrincipal', 'm')
            ->where('c.status = :sts')
            ->andWhere('c.fechaHoraProgramada >= :start')
            ->andWhere('c.fechaHoraProgramada <= :end')
            ->setParameter('sts', $status)
            ->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'))
            ->orderBy('c.fechaHoraProgramada', 'DESC');

        if ($estado !== null) {
            $qb->andWhere('c.estado = :estado')
               ->setParameter('estado', $estado);
        }

        return $qb->getQuery()->getResult();
    }

    public function createDataTablesQueryBuilder(\Doctrine\ORM\QueryBuilder $qb, \DateTime $start, \DateTime $end, ?\App\Enum\CirugiaEstados $estado = null)
    {
        $qb->select('c')
           ->from(\App\Entity\Cirugia::class, 'c')
           ->where('c.status = :sts')
           ->andWhere('c.fechaHoraProgramada >= :start')
           ->andWhere('c.fechaHoraProgramada <= :end')
           ->setParameter('sts', $this->getEntityManager()->getRepository(\App\Entity\StatusRecord::class)->getActive())
           ->setParameter('start', $start->format('Y-m-d 00:00:00'))
           ->setParameter('end', $end->format('Y-m-d 23:59:59'));

        if ($estado !== null) {
            $qb->andWhere('c.estado = :estado')
               ->setParameter('estado', $estado);
        }
    }
}

