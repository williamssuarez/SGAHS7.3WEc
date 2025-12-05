<?php

namespace App\Repository;

use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatusRecord>
 */
class StatusRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusRecord::class);
    }

    public function getActive(){
        return $this->_getByCode('ACTRECORD');
    }

    public function getRemove(){
        return $this->_getByCode('REMRECORD');
    }

    public function getDisabled(){
        return $this->_getByCode('DISHRECORD');
    }

    public function getLockedUser(){
        return $this->_getByCode('NLOKREC');
    }

    private function _getByCode($code){

        $qb = $this->createQueryBuilder('_u')
            ->select('_u')
            ->where('_u.codigo = :rcode')
            ->setParameter('rcode', $code);

        $query = $qb->getQuery();

        return $query->setMaxResults(1)->getOneOrNullResult();
    }
}
