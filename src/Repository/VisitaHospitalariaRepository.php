<?php

namespace App\Repository;

use App\Entity\VisitaHospitalaria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VisitaHospitalaria>
 */
class VisitaHospitalariaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisitaHospitalaria::class);
    }

    public function getHistoricalVisitsByDate(\DateTime $start, \DateTime $end, \App\Entity\StatusRecord $status): array
    {
        return $this->createQueryBuilder('v')
            ->select('v', 'h', 'p', 'c', 'hab', 'a') // Eager load
            ->join('v.hospitalizacion', 'h')
            ->join('h.paciente', 'p')
            ->join('h.camaActual', 'c')
            ->join('c.habitacion', 'hab')
            ->join('hab.area', 'a')
            ->where('v.status = :sts')
            ->andWhere('v.estado = :estado')
            ->andWhere('v.fechaHoraEntrada >= :start')
            ->andWhere('v.fechaHoraEntrada <= :end')
            ->setParameter('sts', $status)
            ->setParameter('estado', 'FINALIZADA')
            ->setParameter('start', $start->format('Y-m-d 00:00:00'))
            ->setParameter('end', $end->format('Y-m-d 23:59:59'))
            ->orderBy('v.fechaHoraEntrada', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
