<?php

namespace App\Service;

use App\Entity\Citas;
use App\Entity\CitasSolicitudes;
use App\Entity\Consulta;
use App\Entity\Consultorios;
use App\Entity\Especialidades;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use App\Entity\TurnoDoctor;
use App\Entity\User;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use App\Enum\CitasSolicitudesEstados;
use App\Enum\ConsultaEstados;
use App\Repository\CitasRepository;
use App\Repository\ConsultaRepository;
use App\Repository\InternalProfileRepository;
use App\Repository\TurnoDoctorRepository;
use Doctrine\ORM\EntityManagerInterface;

class AppointmentReassignService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AuditService $auditService,
        private readonly InternalProfileRepository $internalProfileRepository,
        private readonly TurnoDoctorRepository $turnoDoctorRepository,
        private readonly CitasRepository $citasRepository,
        private readonly ConsultaRepository $consultaRepository,
    ) {
    }

    /**
     * Obtiene los médicos elegibles para una especialidad en una fecha dada (por defecto hoy).
     * Devuelve una lista estructurada donde los médicos con turno activo en el día aparecen primero y marcados.
     */
    public function getEligibleDoctors(Especialidades $especialidad, ?\DateTimeInterface $date = null): array
    {
        $targetDate = $date ?? new \DateTime('today', new \DateTimeZone('America/Caracas'));
        $dayOfWeek = (int) $targetDate->format('N'); // 1 (Lun) - 7 (Dom)

        // 1. Obtener todos los doctores activos de la especialidad
        $doctors = $this->internalProfileRepository
            ->getDoctorsByEspecialidadQueryBuilder($especialidad)
            ->getQuery()
            ->getResult();

        // 2. Obtener los turnos activos para ese día de la semana y especialidad
        $activeTurnos = $this->turnoDoctorRepository->findBy([
            'especialidad' => $especialidad,
            'dayOfWeek' => $dayOfWeek,
            'isActive' => true,
            'status' => $this->em->getRepository(StatusRecord::class)->getActive(),
        ]);

        $doctorShifts = [];
        foreach ($activeTurnos as $turno) {
            if ($turno->getDoctor()) {
                $docId = $turno->getDoctor()->getId();
                $shiftText = sprintf(
                    '%s - %s (%s)',
                    $turno->getStartTime()->format('h:i A'),
                    $turno->getEndTime()->format('h:i A'),
                    $turno->getConsultorio() ? $turno->getConsultorio()->getNombre() : 'Sin consultorio'
                );
                $doctorShifts[$docId][] = $shiftText;
            }
        }

        $result = [];
        foreach ($doctors as $doctor) {
            $docId = $doctor->getId();
            $hasShift = isset($doctorShifts[$docId]);
            $result[] = [
                'id' => $docId,
                'nombre' => $doctor->getNombre() . ' ' . $doctor->getApellido(),
                'hasShiftToday' => $hasShift,
                'shiftDetails' => $hasShift ? implode(', ', $doctorShifts[$docId]) : null,
            ];
        }

        // Ordenar: primero los que tienen turno hoy, luego por nombre alfabético
        usort($result, function ($a, $b) {
            if ($a['hasShiftToday'] !== $b['hasShiftToday']) {
                return $b['hasShiftToday'] <=> $a['hasShiftToday'];
            }
            return strcmp($a['nombre'], $b['nombre']);
        });

        return $result;
    }

    /**
     * Reasigna / deriva una consulta en sala de espera a un nuevo médico.
     */
    public function reassignConsulta(Consulta $consulta, InternalProfile $newDoctor, string $motivo, User $user): void
    {
        if ($consulta->getEstadoConsulta() !== ConsultaEstados::PENDING) {
            throw new \InvalidArgumentException('Solo se pueden derivar consultas que se encuentren en Sala de Espera (estado Pendiente).');
        }

        $prevDoctor = $consulta->getDoctor();
        if ($prevDoctor && $prevDoctor->getId() === $newDoctor->getId()) {
            throw new \InvalidArgumentException('El médico de destino debe ser diferente al médico actual.');
        }

        // Actualizar médico en la consulta
        $consulta->setDoctor($newDoctor);

        $nowStr = (new \DateTime('now', new \DateTimeZone('America/Caracas')))->format('d/m/Y h:i A');
        $prevDoctorName = $prevDoctor ? $prevDoctor->getNombre() . ' ' . $prevDoctor->getApellido() : 'Sin asignar';
        $newDoctorName = $newDoctor->getNombre() . ' ' . $newDoctor->getApellido();

        $traceNote = sprintf('[%s] Derivado de Dr. %s a Dr. %s. Motivo: %s', $nowStr, $prevDoctorName, $newDoctorName, $motivo);

        $currentObs = $consulta->getObservacion();
        $consulta->setObservacion($currentObs ? $currentObs . ' | ' . $traceNote : $traceNote);

        // Si la consulta tiene una Cita vinculada, sincronizar también el doctor en la Cita
        $cita = $consulta->getCita();
        if ($cita) {
            $cita->setDoctor($newDoctor);
            $citaObs = $cita->getObservaciones();
            $cita->setObservaciones($citaObs ? $citaObs . ' | ' . $traceNote : $traceNote);
            $this->em->persist($cita);
        }

        // Auditoría
        $this->auditService->persistAudit(
            AuditTipos::CONSULT_REASSIGNED,
            sprintf('Consulta transferida del Dr. %s al Dr. %s. Motivo: %s', $prevDoctorName, $newDoctorName, $motivo),
            $consulta->getPaciente(),
            $consulta,
            null,
            null,
            null,
            null,
            $user,
            $user->getInternalProfile()
        );

        $this->em->persist($consulta);
        $this->em->flush();
    }

    /**
     * Reasigna el médico asignado a una cita médica programada.
     */
    public function reassignCita(Citas $cita, InternalProfile $newDoctor, string $motivo, User $user): void
    {
        if ($cita->getEstadoCita() === CitasEstados::COMPLETED || $cita->getEstadoCita() === CitasEstados::CANCELED) {
            throw new \InvalidArgumentException('No se puede reasignar una cita que ya ha sido completada o cancelada.');
        }

        $prevDoctor = $cita->getDoctor();
        if ($prevDoctor && $prevDoctor->getId() === $newDoctor->getId()) {
            throw new \InvalidArgumentException('El médico de destino debe ser diferente al médico actual.');
        }

        $nowStr = (new \DateTime('now', new \DateTimeZone('America/Caracas')))->format('d/m/Y h:i A');
        $prevDoctorName = $prevDoctor ? $prevDoctor->getNombre() . ' ' . $prevDoctor->getApellido() : 'Sin asignar';
        $newDoctorName = $newDoctor->getNombre() . ' ' . $newDoctor->getApellido();

        $traceNote = sprintf('[%s] Reasignada de Dr. %s a Dr. %s. Motivo: %s', $nowStr, $prevDoctorName, $newDoctorName, $motivo);

        $cita->setDoctor($newDoctor);
        $currentObs = $cita->getObservaciones();
        $cita->setObservaciones($currentObs ? $currentObs . ' | ' . $traceNote : $traceNote);

        // Sincronizar consulta vinculada si ya existiera
        $consulta = $cita->getConsulta();
        if ($consulta && $consulta->getEstadoConsulta() === ConsultaEstados::PENDING) {
            $consulta->setDoctor($newDoctor);
            $cObs = $consulta->getObservacion();
            $consulta->setObservacion($cObs ? $cObs . ' | ' . $traceNote : $traceNote);
            $this->em->persist($consulta);
        }

        // Auditoría
        $this->auditService->persistAudit(
            AuditTipos::APPOINTMENT_REASSIGNED,
            sprintf('Cita reasignada del Dr. %s al Dr. %s. Motivo: %s', $prevDoctorName, $newDoctorName, $motivo),
            $cita->getPaciente(),
            $consulta,
            null,
            null,
            null,
            null,
            $user,
            $user->getInternalProfile()
        );

        $this->em->persist($cita);
        $this->em->flush();
    }

    /**
     * Reasignación masiva / contingencia de jornada:
     * Transfiere todas las consultas en espera (PENDING) y citas programadas (EXPECTED) de hoy
     * de un médico a otro médico.
     */
    public function batchReassignDoctorToday(InternalProfile $originDoctor, InternalProfile $targetDoctor, string $motivo, User $user): array
    {
        if ($originDoctor->getId() === $targetDoctor->getId()) {
            throw new \InvalidArgumentException('El médico de destino debe ser diferente al médico de origen.');
        }

        $today = new \DateTime('today', new \DateTimeZone('America/Caracas'));
        $startOfDay = (clone $today)->setTime(0, 0, 0);
        $endOfDay = (clone $today)->setTime(23, 59, 59);

        $nowStr = (new \DateTime('now', new \DateTimeZone('America/Caracas')))->format('d/m/Y h:i A');
        $originName = $originDoctor->getNombre() . ' ' . $originDoctor->getApellido();
        $targetName = $targetDoctor->getNombre() . ' ' . $targetDoctor->getApellido();

        $batchTraceNote = sprintf('[%s Contingencia] Transferido en lote de Dr. %s a Dr. %s. Motivo: %s', $nowStr, $originName, $targetName, $motivo);

        // 1. Reasignar Consultas en espera (PENDING)
        $consultas = $this->em->getRepository(Consulta::class)->findBy([
            'doctor' => $originDoctor,
            'estadoConsulta' => ConsultaEstados::PENDING,
            'status' => $this->em->getRepository(StatusRecord::class)->getActive(),
        ]);

        $consultasCount = 0;
        foreach ($consultas as $consulta) {
            $consulta->setDoctor($targetDoctor);
            $cObs = $consulta->getObservacion();
            $consulta->setObservacion($cObs ? $cObs . ' | ' . $batchTraceNote : $batchTraceNote);

            if ($consulta->getCita()) {
                $cita = $consulta->getCita();
                $cita->setDoctor($targetDoctor);
                $citObs = $cita->getObservaciones();
                $cita->setObservaciones($citObs ? $citObs . ' | ' . $batchTraceNote : $batchTraceNote);
                $this->em->persist($cita);
            }

            $this->em->persist($consulta);
            $consultasCount++;
        }

        // 2. Reasignar Citas de hoy en EXPECTED
        $qb = $this->em->getRepository(Citas::class)->createQueryBuilder('c')
            ->where('c.doctor = :originDoctor')
            ->andWhere('c.estadoCita = :expectedState')
            ->andWhere('c.fecha BETWEEN :startOfDay AND :endOfDay')
            ->andWhere('c.status = :sts')
            ->setParameter('originDoctor', $originDoctor)
            ->setParameter('expectedState', CitasEstados::EXPECTED)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('sts', $this->em->getRepository(StatusRecord::class)->getActive());

        $citasHoy = $qb->getQuery()->getResult();
        $citasCount = 0;
        foreach ($citasHoy as $cita) {
            $cita->setDoctor($targetDoctor);
            $citObs = $cita->getObservaciones();
            $cita->setObservaciones($citObs ? $citObs . ' | ' . $batchTraceNote : $batchTraceNote);
            $this->em->persist($cita);
            $citasCount++;
        }

        // 3. Auditoría del lote
        $totalTransferidos = $consultasCount + $citasCount;
        $this->auditService->persistAudit(
            AuditTipos::APPOINTMENT_BATCH_REASSIGNED,
            sprintf(
                'Contingencia Médica: Transferidos %d pacientes (%d en espera, %d citas de hoy) del Dr. %s al Dr. %s. Motivo: %s',
                $totalTransferidos,
                $consultasCount,
                $citasCount,
                $originName,
                $targetName,
                $motivo
            ),
            null,
            null,
            null,
            null,
            null,
            null,
            $user,
            $user->getInternalProfile()
        );

        $this->em->flush();

        return [
            'total' => $totalTransferidos,
            'consultas' => $consultasCount,
            'citas' => $citasCount,
        ];
    }

    /**
     * Reprogramación manual de una cita a una nueva fecha y hora.
     */
    public function rescheduleCitaManually(
        Citas $cita,
        \DateTimeInterface $newDate,
        \DateTimeInterface $newStart,
        \DateTimeInterface $newEnd,
        ?InternalProfile $newDoctor,
        ?Consultorios $newConsultorio,
        string $motivo,
        User $user
    ): void {
        if ($cita->getEstadoCita() === CitasEstados::COMPLETED) {
            throw new \InvalidArgumentException('No se puede reprogramar una cita ya completada.');
        }

        $today = new \DateTime('today', new \DateTimeZone('America/Caracas'));
        if ($newDate < $today) {
            throw new \InvalidArgumentException('La nueva fecha de la cita no puede ser en el pasado.');
        }

        $prevInfo = sprintf(
            '%s de %s a %s (Dr. %s)',
            $cita->getFecha()->format('d/m/Y'),
            $cita->getHoraInicio()->format('h:i A'),
            $cita->getHoraFin() ? $cita->getHoraFin()->format('h:i A') : '--:--',
            $cita->getDoctor() ? $cita->getDoctor()->getNombre() . ' ' . $cita->getDoctor()->getApellido() : 'Sin asignar'
        );

        $cita->setFecha($newDate);
        $cita->setHoraInicio($newStart);
        $cita->setHoraFin($newEnd);

        if ($newDoctor) {
            $cita->setDoctor($newDoctor);
        }
        if ($newConsultorio) {
            $cita->setConsultorio($newConsultorio);
        }

        // Si estaba en CHECKED_IN pero no fue atendida y se reprograma, se restablece a EXPECTED y se desvincula consulta pendiente
        if ($cita->getEstadoCita() === CitasEstados::CHECKED_IN) {
            $cita->setEstadoCita(CitasEstados::EXPECTED);
            if ($cita->getConsulta() && $cita->getConsulta()->getEstadoConsulta() === ConsultaEstados::PENDING) {
                $this->em->remove($cita->getConsulta());
                $cita->setConsulta(null);
            }
        }

        $nowStr = (new \DateTime('now', new \DateTimeZone('America/Caracas')))->format('d/m/Y h:i A');
        $traceNote = sprintf(
            '[%s Reprogramación Manual] Anterior: [%s] -> Nueva: [%s %s]. Motivo: %s',
            $nowStr,
            $prevInfo,
            $newDate->format('d/m/Y'),
            $newStart->format('h:i A'),
            $motivo
        );

        $currentObs = $cita->getObservaciones();
        $cita->setObservaciones($currentObs ? $currentObs . ' | ' . $traceNote : $traceNote);

        $this->auditService->persistAudit(
            AuditTipos::APPOINTMENT_RESCHEDULED,
            sprintf('Cita reprogramada manualmente. De [%s] a [%s %s]. Motivo: %s', $prevInfo, $newDate->format('d/m/Y'), $newStart->format('h:i A'), $motivo),
            $cita->getPaciente(),
            null,
            null,
            null,
            null,
            null,
            $user,
            $user->getInternalProfile()
        );

        $this->em->persist($cita);
        $this->em->flush();
    }

    /**
     * Devolución de la solicitud de cita a la cola del Scheduler automático con máxima prioridad.
     */
    public function returnCitaToSchedulerQueue(Citas $cita, string $motivo, User $user): void
    {
        if ($cita->getEstadoCita() === CitasEstados::COMPLETED) {
            throw new \InvalidArgumentException('No se puede reencolar una cita que ya fue completada.');
        }

        $solicitud = $cita->getSolicitud();
        if (!$solicitud) {
            throw new \InvalidArgumentException('La cita no tiene una solicitud vinculada para devolver a la cola.');
        }

        $nowStr = (new \DateTime('now', new \DateTimeZone('America/Caracas')))->format('d/m/Y h:i A');

        // Cancelar la cita actual por reprogramación
        $cita->setEstadoCita(CitasEstados::CANCELED);
        $cancelNote = sprintf('[%s Reprogramación por Cola] Cita liberada para reprogramación automática. Motivo: %s', $nowStr, $motivo);
        $currentObs = $cita->getObservaciones();
        $cita->setObservaciones($currentObs ? $currentObs . ' | ' . $cancelNote : $cancelNote);

        // Desvincular de la consulta si existía en estado PENDING
        if ($cita->getConsulta() && $cita->getConsulta()->getEstadoConsulta() === ConsultaEstados::PENDING) {
            $this->em->remove($cita->getConsulta());
            $cita->setConsulta(null);
        }

        // Devolver la solicitud a PENDING y otorgar score de máxima prioridad
        $solicitud->setEstadoSolicitud(CitasSolicitudesEstados::PENDING);
        $solicitudMotivo = sprintf('%s [Reprogramación prioritaria: %s]', $solicitud->getMotivoConsulta(), $motivo);
        $solicitud->setMotivoConsulta($solicitudMotivo);

        // Al adelantar artificialmente la fecha de creación en 30 días en el pasado aseguramos máxima prioridad en el scheduler
        // sin romper tipos de datos
        $solicitud->setScorePrioridad(9999);

        $this->auditService->persistAudit(
            AuditTipos::APPOINTMENT_RESCHEDULED,
            sprintf('Cita enviada a reprogramación automática por Scheduler. Motivo: %s', $motivo),
            $cita->getPaciente(),
            null,
            null,
            null,
            null,
            null,
            $user,
            $user->getInternalProfile()
        );

        $this->em->persist($cita);
        $this->em->persist($solicitud);
        $this->em->flush();
    }
}
