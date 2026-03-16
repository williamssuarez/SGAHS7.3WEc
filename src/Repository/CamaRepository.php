<?php

namespace App\Repository;

use App\Entity\Cama;
use App\Entity\StatusRecord;
use App\Enum\CamaEstados;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cama>
 */
class CamaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cama::class);
    }

    public function getActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->distinct()
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.codigo', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query;
    }

    public function getAvailableActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->distinct()
            ->select('u')
            ->join('c.zona', 'z')

            ->where('u.status = :sts')
            ->andWhere('c.estado = :state')

            ->orderBy('z.nombre', 'ASC')
            ->addOrderBy('c.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('state', CamaEstados::AVAILABLE->value)
        ;

        return $query;
    }

    public function getActivesforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.codigo', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query->getQuery()->getResult();
    }
}
