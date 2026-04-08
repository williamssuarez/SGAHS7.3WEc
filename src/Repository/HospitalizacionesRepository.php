<?php

namespace App\Repository;

use App\Entity\Hospitalizaciones;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use App\Enum\HospitalizacionEstados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hospitalizaciones>
 */
class HospitalizacionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hospitalizaciones::class);
    }

    public function getActivesforTableByState($state)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.estado = :state')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('state', $state)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Finds all patients currently admitted to a physical bed.
     * Eagerly loads the bed, room, and area to prevent N+1 performance issues.
     * Orders them logically by geography.
     */
    public function findActiveCensus(): array
    {
        return $this->createQueryBuilder('h')
            ->select('h', 'c', 'hab', 'a', 'p', 'med') // Eager load the relations!
            ->join('h.camaActual', 'c')
            ->join('c.habitacion', 'hab')
            ->join('hab.area', 'a')
            ->join('h.paciente', 'p')
            ->leftJoin('h.medicoTratante', 'med') // Left join in case no doctor is assigned yet

            // Only fetch patients actively in a bed
            ->where('h.estado = :state')
            ->andWhere('h.status = :sts')

            ->setParameter('state', HospitalizacionEstados::ADMITTED->value)
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())

            // Order geographically so the grouping in the Controller works perfectly
            ->orderBy('a.nombre', 'ASC')
            ->addOrderBy('hab.nombre', 'ASC')
            ->addOrderBy('c.codigo', 'ASC')

            ->getQuery()
            ->getResult();
    }
}
