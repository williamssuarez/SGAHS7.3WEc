<?php

namespace App\Repository;

use App\Entity\Habitacion;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Habitacion>
 */
class HabitacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Habitacion::class);
    }

    public function getActivesforSelect()
    {
        return $this->createQueryBuilder('u')
            ->addSelect('a') // Eager load the Area so Doctrine doesn't make 100 extra queries
            ->join('u.area', 'a')
            ->where('u.status = :sts')
            ->orderBy('a.nombre', 'ASC') // First sort by Area Name
            ->addOrderBy('u.nombre', 'ASC') // Then sort by Room Name
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive());
    }

    public function getActivesforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query->getQuery()->getResult();
    }
}
