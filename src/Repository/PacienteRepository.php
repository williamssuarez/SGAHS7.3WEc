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

    public function createDataTablesQueryBuilder(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('p')
           ->from(Paciente::class, 'p')
           ->where('p.status = :sts')
           ->setParameter('sts', $this->getEntityManager()->getRepository(StatusRecord::class)->getActive());
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

    public function findByNombreLike(?string $search, int $maxResults = 15): array
    {
        if ($search === null || trim($search) === '') {
            return [];
        }

        $search = trim($search);
        $qb = $this->createQueryBuilder('p'); // 'p' for Paciente

        // Dividir la búsqueda en palabras individuales para permitir buscar "Nombre Apellido"
        $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $i => $word) {
            $wordLower = mb_strtolower($word, 'UTF-8');
            $cleanDigits = preg_replace('/\D/', '', $word);

            $paramWord = 'search_word_' . $i;

            $orConditions = [
                "LOWER(p.nombre) LIKE :$paramWord",
                "LOWER(p.apellido) LIKE :$paramWord",
            ];

            $qb->setParameter($paramWord, '%' . $wordLower . '%');

            // Si la palabra contiene números, permitir búsqueda en cédula (incluso si escribieron 30.525.051 o V-30525051)
            if ($cleanDigits !== '') {
                $paramCedula = 'search_cedula_' . $i;
                $orConditions[] = "p.cedula LIKE :$paramCedula";
                $qb->setParameter($paramCedula, '%' . $cleanDigits . '%');
            }

            $qb->andWhere($qb->expr()->orX(...$orConditions));
        }

        return $qb
            ->andWhere('p.status = :sts')
            ->andWhere(
                $qb->expr()->orX(
                    'p.fallecido = false',
                    'p.fallecido IS NULL'
                )
            )
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
