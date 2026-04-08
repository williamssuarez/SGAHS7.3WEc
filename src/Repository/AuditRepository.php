<?php

namespace App\Repository;

use App\Entity\Audit;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Audit>
 */
class AuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Audit::class);
    }

    public function getActivesforTableByDateOnly(\DateTime $from, \DateTime $to, $userId = null)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.created between :from AND :to')
            ->orderBy('u.id', 'DESC');

        if ($userId){
            $qb->andWhere('u.uidCreate = :user')
            ->setParameter('user', $userId);
        }

            $qb->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }

    public function getActivesforTableByState($state, \DateTime $from, \DateTime $to, $userId = null)
    {
        $qb = $this->createQueryBuilder('u');

        $query = $qb
            ->select('u')

            ->where('u.status = :sts')
            ->andWhere('u.tipoAudit = :state')
            ->andWhere('u.created between :from AND :to')
            ->orderBy('u.id', 'DESC');

        if ($userId){
            $qb->andWhere('u.uidCreate = :user')
                ->setParameter('user', $userId);
        }

            $qb->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->setParameter('state', $state)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;

        return $query->getQuery()->getResult();
    }
}
