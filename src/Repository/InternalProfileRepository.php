<?php

namespace App\Repository;

use App\Entity\Especialidades;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InternalProfile>
 */
class InternalProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InternalProfile::class);
    }

    public function getUserByValueforCheck($field, $value, $id = null, $extraField = null, $extraValue = null)
    {
        $qb = $this->createQueryBuilder('u');
        $whereField = 'u.' . $field . ' = :value';

        $query = $qb
            ->select('u')

            ->where($whereField);

        if ($extraField) {
            $extra = 'u.' . $extraField . ' = :extra';
            $qb->andWhere($extra)
                ->setParameter('extra', $extraValue);
        }

        if ($id){
            $qb->andWhere('u.id != :profileId')
                ->setParameter('profileId', $id);
        }

        $qb->setParameter('value', $value)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    public function getDoctorsByEspecialidadQueryBuilder(?Especialidades $especialidad)
    {
        $qb = $this->createQueryBuilder('ip');

        if (!$especialidad) {
            return $qb->where('1 = 0'); // Returns an empty result if no specialty is selected
        }

        return $qb
            ->join('ip.especialidades', 'e')
            ->join('ip.webUser', 'u')
            ->where('e = :especialidad')
            ->setParameter('especialidad', $especialidad)
            // FIX: Explicitly cast the JSON column to text before applying LIKE
            ->andWhere('CAST(u.roles AS text) LIKE :role1 OR CAST(u.roles AS text) LIKE :role2 OR CAST(u.roles AS text) LIKE :role3')
            ->andWhere('u.status = :sts')
            ->setParameter('role1', '%"ROLE_DOCTOR"%')
            ->setParameter('role2', '%"ROLE_ER_DOCTOR"%')
            ->setParameter('role3', '%"ROLE_DOCTOR_QUIROFANO"%')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->orderBy('ip.nombre', 'ASC');
    }
}
