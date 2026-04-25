<?php

namespace App\Repository;

use App\Entity\Cirugia;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cirugia>
 */
class CirugiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cirugia::class);
    }

    public function findDailySchedule(\DateTime $today)
    {
        $from = clone $today->setTime(0, 0, 0);
        $to = clone $today->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.fechaHoraProgramada between :from and :to')

            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }

//    /**
//     * @return Cirugia[] Returns an array of Cirugia objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cirugia
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
