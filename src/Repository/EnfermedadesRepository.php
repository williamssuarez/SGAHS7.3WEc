<?php

namespace App\Repository;

use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enfermedades>
 */
class EnfermedadesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enfermedades::class);
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

//    /**
//     * @return Enfermedades[] Returns an array of Enfermedades objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Enfermedades
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
