<?php

namespace App\Repository;

use App\Entity\HistoriaPaciente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriaPaciente>
 */
class HistoriaPacienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriaPaciente::class);
    }

    /**
     * Finds all history records for a given patient, ordered by date descending.
     *
     * @param int $pacienteId
     * @return HistoriaPaciente[]
     */
    public function findByPacienteOrderedByDate(int $pacienteId): array
    {
        return $this->createQueryBuilder('h')
            // 1. Filter by the patient ID (assuming 'paciente' is the field name)
            ->andWhere('h.paciente = :pacienteId')
            ->setParameter('pacienteId', $pacienteId)

            // 2. Order by the date field, newest first
            ->orderBy('h.fecha_atendido', 'DESC')

            // 3. Execute the query
            ->getQuery()
            ->getResult();
    }
}
