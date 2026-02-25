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

    public function getPatientbyValueforCheck($field, $value, $id = null, $extraField = null, $extraValue = null)
    {
        $qb = $this->createQueryBuilder('u');
        $whereField = 'u.' . $field . ' = :value';

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere($whereField);

        if ($extraField) {
            $extra = 'u.' . $extraField . ' = :extra';
            $qb->andWhere($extra)
            ->setParameter('extra', $extraValue);
        }

        if ($id){
            $qb->andWhere('u.id != :patientId')
            ->setParameter('patientId', $id);
        }

        $qb->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ->setParameter('value', $value)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findByNombreLike(string $search, int $maxResults = 15): array
    {
        $qb = $this->createQueryBuilder('p'); // 'p' for Paciente

        return $qb
            ->where(
                $qb->expr()->orX(
                    'p.nombre LIKE :search',
                    'p.apellido LIKE :search',
                    'p.cedula LIKE :search'
                )
            )
            ->andWhere('p.status = :sts')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())

            ->orderBy('p.nombre', 'ASC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    public function findByCedulaLike(string $search)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.cedula = :search')
            ->andWhere('u.status = :sts')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('search', '%' . $search . '%')
        ;

        return $query->getQuery()->getResult();
    }
}
