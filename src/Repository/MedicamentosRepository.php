<?php

namespace App\Repository;

use App\Entity\Medicamentos;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medicamentos>
 */
class MedicamentosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicamentos::class);
    }

    public function getActivesforSelect()
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->distinct()
            ->select('u')

            ->where('u.status = :sts')
            ->addOrderBy('u.nombre', 'ASC')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
        ;

        return $query;
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

//    /**
//     * @return Medicamentos[] Returns an array of Medicamentos objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Medicamentos
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
