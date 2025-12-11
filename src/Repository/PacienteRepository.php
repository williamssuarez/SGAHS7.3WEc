<?php

namespace App\Repository;

use App\Entity\Paciente;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paciente>
 */
class PacienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paciente::class);
    }

    public function getActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->distinct()
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive());
    }

    public function getActivesforTable()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query->getQuery()->getResult();
    }

    public function getPatientbyValueforCheck($field, $value, $id)
    {
        $qb = $this->createQueryBuilder('u');
        $whereField = 'u.' . $field . ' = :value';

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere($whereField)
            ->andWhere('u.id != :patientId')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('value', $value)
            ->setParameter('patientId', $id)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }
}
