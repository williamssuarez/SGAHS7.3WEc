<?php

namespace App\Repository;

use App\Entity\Alergias;
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

    public function getActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->distinct()
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.name', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository('CoreMainBundle:StatusRecord')->getActive())
        ;

        return $query;
    }
}
