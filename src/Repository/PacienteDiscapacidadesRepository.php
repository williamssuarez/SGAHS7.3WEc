<?php

namespace App\Repository;

use App\Entity\PacienteDiscapacidades;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PacienteDiscapacidades>
 */
class PacienteDiscapacidadesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PacienteDiscapacidades::class);
    }

    public function getActivesforTable($id)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u,d,p')

            ->innerJoin('u.discapacidad', 'd')
            ->innerJoin('u.paciente', 'p')

            ->where('u.status = :sts')
            ->andWhere('d.status = :sts')
            ->andWhere('p.status = :sts')
            ->andWhere('p.id = :id')

            ->addOrderBy('u.id', 'DESC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('id', $id)
        ;

        return $query->getQuery()->getResult();
    }
}
