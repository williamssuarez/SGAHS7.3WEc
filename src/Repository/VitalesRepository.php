<?php

namespace App\Repository;

use App\Entity\StatusRecord;
use App\Entity\Vitales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vitales>
 */
class VitalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vitales::class);
    }

    public function getActivesforTable($id)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,c,p')

            ->innerJoin('u.consulta', 'c')
            ->innerJoin('c.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')
            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }
}
