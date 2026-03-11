<?php

namespace App\Repository;

use App\Entity\Alergias;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alergias>
 */
class AlergiasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alergias::class);
    }

    public function getActivesforTable($id)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,a,p')

            ->innerJoin('u.alergeno', 'a')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('a.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }
}
