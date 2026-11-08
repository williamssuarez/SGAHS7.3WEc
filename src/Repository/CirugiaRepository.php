<?php

namespace App\Repository;

use App\Entity\Cirugia;
use App\Entity\Quirofano;
use App\Entity\StatusRecord;
use App\Enum\CirugiaEstados;
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

    public function findDailySchedule(\DateTimeInterface $today): array
    {
        $from = (clone $today)->setTime(0, 0, 0);
        $to = (clone $today)->setTime(23, 59, 59);

        $activeStates = [
            CirugiaEstados::EN_SALA,
            CirugiaEstados::PRE_OP,
            CirugiaEstados::TRANS_OP,
            CirugiaEstados::POST_OP,
        ];

        $qb = $this->createQueryBuilder('c');

        return $qb
            ->select('c', 'p', 'q', 'm')
            ->leftJoin('c.paciente', 'p')
            ->leftJoin('c.quirofano', 'q')
            ->leftJoin('c.cirujanoPrincipal', 'm')
            ->where('c.status = :sts')
            ->andWhere('(c.fechaHoraProgramada BETWEEN :from AND :to) OR (c.fechaHoraProgramada < :from AND c.estado IN (:activeStates))')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('activeStates', $activeStates)
            ->orderBy('c.fechaHoraProgramada', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveSurgeryInQuirofano(Quirofano $room, ?int $excludeCirugiaId = null): ?Cirugia
    {
        $activeStates = [
            CirugiaEstados::EN_SALA,
            CirugiaEstados::PRE_OP,
            CirugiaEstados::TRANS_OP,
            CirugiaEstados::POST_OP,
        ];

        $qb = $this->createQueryBuilder('c')
            ->select('c', 'p')
            ->leftJoin('c.paciente', 'p')
            ->where('c.quirofano = :room')
            ->andWhere('c.estado IN (:activeStates)')
            ->andWhere('c.status = :sts')
            ->setParameter('room', $room)
            ->setParameter('activeStates', $activeStates)
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive());

        if ($excludeCirugiaId !== null) {
            $qb->andWhere('c.id != :currentId')
               ->setParameter('currentId', $excludeCirugiaId);
        }

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
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

