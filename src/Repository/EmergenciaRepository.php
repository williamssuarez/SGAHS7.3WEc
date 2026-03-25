<?php

namespace App\Repository;

use App\Entity\Emergencia;
use App\Entity\StatusRecord;
use App\Enum\EmergenciasEstados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emergencia>
 */
class EmergenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emergencia::class);
    }

    public function getEmergencyByPatient4Check($patientId)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.paciente = :paciente')
            ->andWhere('u.estado != :state')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('paciente', $patientId)
            ->setParameter('state', EmergenciasEstados::DISCHARGED)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getActivesforTableByDateOnly(\DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.fechaIngreso between :from AND :to')
            ->andWhere('u.estado = :estado')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('estado', EmergenciasEstados::DISCHARGED)
        ;

        return $query->getQuery()->getResult();
    }

    public function getActivesforTableByState($state, \DateTime $from, \DateTime $to)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,a')

            ->innerJoin('u.altaMedica', 'a')

            ->where('u.status = :sts')
            ->andWhere('u.estado = :estado')
            ->andWhere('a.condicionAlta = :state')
            ->andWhere('u.fechaIngreso between :from AND :to')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('estado', EmergenciasEstados::DISCHARGED)
            ->setParameter('state', $state)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }
}
