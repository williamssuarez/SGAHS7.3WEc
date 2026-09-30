<?php

namespace App\Repository;

use App\Entity\HorarioVisitas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HorarioVisitas>
 */
class HorarioVisitasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HorarioVisitas::class);
    }

    /**
     * Checks if a given area is open for visits at the provided time.
     */
    public function isAreaOpenForVisits(\App\Entity\Area $area, \DateTimeInterface $currentTime): bool
    {
        // 1 (for Monday) through 7 (for Sunday)
        $currentDay = (int) $currentTime->format('N');
        // Extract just the time part for comparison (HH:mm:ss)
        $timeString = $currentTime->format('H:i:s');

        $result = $this->createQueryBuilder('h')
            ->select('count(h.id)')
            ->where('h.area = :area')
            ->andWhere('h.diaSemana = :day')
            ->andWhere('h.horaInicio <= :time')
            ->andWhere('h.horaFin >= :time')
            ->setParameter('area', $area)
            ->setParameter('day', $currentDay)
            ->setParameter('time', $timeString)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
