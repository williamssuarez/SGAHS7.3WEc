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
            ->andWhere('CAST(u.roles AS text) LIKE :role1 OR CAST(u.roles AS text) LIKE :role2 OR CAST(u.roles AS text) LIKE :role3 OR CAST(u.roles AS text) LIKE :role4 OR CAST(u.roles AS text) LIKE :role5')
            ->andWhere('u.status = :sts')
            ->setParameter('role1', '%"ROLE_DOCTOR"%')
            ->setParameter('role2', '%"ROLE_ER_DOCTOR"%')
            ->setParameter('role3', '%"ROLE_SURGEON"%')
            ->setParameter('role4', '%"ROLE_ANESTHESIOLOGIST"%')
            ->setParameter('role5', '%"ROLE_ADMIN_QUIROFANO"%')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->orderBy('ip.nombre', 'ASC');
    }

    public function getDoctorsByEspecialidadAndActiveShiftQueryBuilder(?Especialidades $especialidad)
    {
        $qb = $this->createQueryBuilder('ip');

        if (!$especialidad) {
            return $qb->where('1 = 0');
        }

        $now = new \DateTime('now', new \DateTimeZone('America/Caracas'));
        $dayOfWeek = (int) $now->format('N'); // 1 (Mon) - 7 (Sun)

        return $qb
            ->join('ip.especialidades', 'e')
            ->join('ip.webUser', 'u')
            ->join('ip.turnoDoctores', 't')
            ->where('e = :especialidad')
            ->andWhere('t.dayOfWeek = :day')
            ->andWhere('t.startTime <= :time')
            ->andWhere('t.endTime >= :time')
            ->andWhere('t.status = :sts')
            ->andWhere('CAST(u.roles AS text) LIKE :role1 OR CAST(u.roles AS text) LIKE :role2 OR CAST(u.roles AS text) LIKE :role3 OR CAST(u.roles AS text) LIKE :role4 OR CAST(u.roles AS text) LIKE :role5')
            ->andWhere('u.status = :sts')
            ->setParameter('especialidad', $especialidad)
            ->setParameter('day', $dayOfWeek)
            ->setParameter('time', $now)
            ->setParameter('role1', '%"ROLE_DOCTOR"%')
            ->setParameter('role2', '%"ROLE_ER_DOCTOR"%')
            ->setParameter('role3', '%"ROLE_SURGEON"%')
            ->setParameter('role4', '%"ROLE_ANESTHESIOLOGIST"%')
            ->setParameter('role5', '%"ROLE_ADMIN_QUIROFANO"%')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive())
            ->orderBy('ip.nombre', 'ASC');
    }

    public function getStaffByRoleQueryBuilder(string $role, string $fallbackRole = null)
    {
        $qb = $this->createQueryBuilder('ip')
            ->join('ip.webUser', 'u')
            ->where('u.status = :sts')
            ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive());

        if ($fallbackRole) {
            $qb->andWhere('CAST(u.roles AS text) LIKE :role OR CAST(u.roles AS text) LIKE :fallbackRole')
               ->setParameter('role', '%"' . $role . '"%')
               ->setParameter('fallbackRole', '%"' . $fallbackRole . '"%');
        } else {
            $qb->andWhere('CAST(u.roles AS text) LIKE :role')
               ->setParameter('role', '%"' . $role . '"%');
        }

        return $qb->orderBy('ip.nombre', 'ASC');
    }
}
