<?php

namespace App\Service;

use App\Entity\Citas;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use Doctrine\ORM\EntityManagerInterface;

readonly class AppointmentCleaner
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuditService $auditService
    ) {}

    public function closeUnattendedAppointments(\DateTimeInterface $targetDate): int
    {
        $start = (clone $targetDate)->setTime(0, 0, 0);
        $end = (clone $targetDate)->setTime(23, 59, 59);

        // 1. Get all 'EXPECTED' appointments for the specific day
        $citas = $this->em->getRepository(Citas::class)->createQueryBuilder('c')
            ->where('c.fecha BETWEEN :start AND :end')
            ->andWhere('c.estadoCita = :estado')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('estado', CitasEstados::EXPECTED)
            ->getQuery()
            ->getResult();

        if (empty($citas)) {
            return 0;
        }

        $count = 0;
        foreach ($citas as $cita) {
            // 2. Mark as canceled (or NO_SHOW)
            $cita->setEstadoCita(CitasEstados::CANCELED);

            // Add a clear system reason so staff knows a human didn't do this
            $cita->setMotivoCancelacion('Cancelación automática: El paciente no se presentó a la cita.');
            $this->em->persist($cita);

            // 3. Audit the event
            $this->auditService->persistAudit(
                AuditTipos::SYSTEM_RECEPTION_AUTO_CANCELED,
                "Cita marcada como no atendida por el cierre automático de fin de día.",
                $cita->getPaciente(),
                null
            );

            $count++;
        }

        $this->em->flush();

        return $count;
    }
}
