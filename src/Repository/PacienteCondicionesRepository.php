<?php

namespace App\Repository;

use App\Entity\PacienteCondiciones;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacienteCondiciones>
 */
class PacienteCondicionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacienteCondiciones::class);
    }

    public function getActivesforTable($id)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,c,p')

            ->innerJoin('u.condicion', 'c')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('c.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }
}
