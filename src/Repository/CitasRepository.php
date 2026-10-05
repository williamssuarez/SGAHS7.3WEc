<?php

namespace App\Repository;

use App\Entity\Citas;
use App\Entity\StatusRecord;
use App\Enum\CitasEstados;
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

    public function getActivesforTableByState($state, \DateTime $from, \DateTime $to)
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

    public function getActivesforTableByDateOnly(\DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.fecha between :from AND :to')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }

    public function getSummaryCountsByState(\DateTime $start, \DateTime $end): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.estadoCita, COUNT(c.id) as total')
            ->where('c.fecha BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.estadoCita')
            ->getQuery()
            ->getResult();

        // Transform results into a clean array: ['expected' => 5, 'finalized' => 10, ...]
        $summary = [
            'expected' => 0,
            'checked_in' => 0,
            'completed' => 0,
            'canceled' => 0,
        ];

        foreach ($results as $row) {
            // Assuming your CitasEstados is an Enumbacked by string
            $stateKey = $row['estadoCita'] instanceof \BackedEnum ? $row['estadoCita']->value : $row['estadoCita'];
            $summary[$stateKey] = (int)$row['total'];
        }

        return $summary;
    }

    public function countAppointmentsByDay(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.fecha, COUNT(c.id) as total')
            ->where('c.fecha BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.fecha')
            ->orderBy('c.fecha', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAppointmentsByDayAndState(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.fecha, c.estadoCita, COUNT(c.id) as total')
            ->where('c.fecha BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->groupBy('c.fecha, c.estadoCita')
            ->orderBy('c.fecha', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Counts assigned appointments grouped by specialty for a given date range.
     */
    public function countAssignedBySpecialty(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('c')
            ->select('e.nombre AS specialtyName', 'COUNT(c.id) AS totalAssigned')
            ->join('c.especialidad', 'e')
            ->andWhere('c.fecha between :start AND :end')
            ->andWhere('c.estadoCita != :canceledState')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('canceledState', \App\Enum\CitasEstados::CANCELED)
            ->groupBy('e.id')
            ->orderBy('totalAssigned', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function createDataTablesStateQueryBuilder(\Doctrine\ORM\QueryBuilder $qb, $state, \DateTime $from, \DateTime $to)
    {
        $qb->select('u')
           ->from(\App\Entity\Citas::class, 'u')
           ->where('u.status = :sts')
           ->andWhere('u.estadoCita = :state')
           ->andWhere('u.fecha between :from AND :to')
           ->setParameter('sts', $this->getEntityManager()->getRepository(\App\Entity\StatusRecord::class)->getActive())
           ->setParameter('state', $state)
           ->setParameter('from', $from)
           ->setParameter('to', $to);
    }

    public function createDataTablesDateOnlyQueryBuilder(\Doctrine\ORM\QueryBuilder $qb, \DateTime $from, \DateTime $to)
    {
        $qb->select('u')
           ->from(\App\Entity\Citas::class, 'u')
           ->where('u.status = :sts')
           ->andWhere('u.fecha between :from AND :to')
           ->setParameter('sts', $this->getEntityManager()->getRepository(\App\Entity\StatusRecord::class)->getActive())
           ->setParameter('from', $from)
           ->setParameter('to', $to);
    }
}

