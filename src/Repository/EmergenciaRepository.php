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
}
