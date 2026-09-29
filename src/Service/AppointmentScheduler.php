<?php

namespace App\Service;

use App\Entity\CitasSolicitudes;
use App\Entity\Citas;
use App\Entity\CitasConfiguraciones;
use App\Entity\TurnoDoctor;
use App\Enum\CitasEstados;
use App\Enum\CitasSolicitudesEstados;
use Doctrine\ORM\EntityManagerInterface;

readonly class AppointmentScheduler
{
    public function __construct(private EntityManagerInterface $em) {}

    public function processQueue(CitasConfiguraciones $config, \DateTime $startDate): int
    {
        // 1. Get pending requests
        $requests = $this->em->getRepository(CitasSolicitudes::class)->findBy([
            'especialidad' => $config->getEspecialidad(),
            'estadoSolicitud' => CitasSolicitudesEstados::PENDING
        ]);

        if (empty($requests)) return 0;

        // 2. Calculate scores and sort
        foreach ($requests as $request) {
            $request->setScorePrioridad($this->calculateScore($request, $config));
        }
        // Highest score at the top (index 0)
        usort($requests, fn($a, $b) => $b->getScorePrioridad() <=> $a->getScorePrioridad());

        $assignedCount = 0;
        $currentDate = clone $startDate;
        $daysLookaheadLimit = 365; // Safety net: don't look more than a year ahead
        $daysChecked = 0;

        // 3. Keep moving forward in time until all requests are scheduled
        while (!empty($requests) && $daysChecked < $daysLookaheadLimit) {

            $currentDayOfWeek = (int) $currentDate->format('N');

            // Find valid slots for this day from active doctors
            $slots = $this->generateSlots($config, $currentDayOfWeek);

            if (!empty($slots)) {
                // Fetch all existing appointments for this specific day from the DB
                $existingCitas = $this->em->getRepository(Citas::class)->findBy([
                    'fecha' => $currentDate,
                    'estadoCita' => CitasEstados::EXPECTED
                ]);

                $newCitasThisRun = []; // Keep track of what we schedule right now in memory

                $dailyAssigned = 0;
                $maxPerDay = $config->getMaxPacientesDia();

                // Loop over requests using their array keys so we can unset them
                foreach ($requests as $requestKey => $request) {

                    if ($dailyAssigned >= $maxPerDay) {
                        break; // Daily limit reached, wait for next day
                    }

                    $assignedSlotKey = null;

                    // Find the first valid slot for this specific patient
                    foreach ($slots as $slotKey => $slot) {
                        if (!$this->hasConflict($slot, $request->getPaciente(), $existingCitas, $newCitasThisRun)) {
                            $assignedSlotKey = $slotKey;
                            break;
                        }
                    }

                    // If we found a valid slot, create the appointment
                    if ($assignedSlotKey !== null) {
                        $slot = $slots[$assignedSlotKey];

                        $cita = new Citas();
                        $cita->setPaciente($request->getPaciente());
                        $cita->setEspecialidad($config->getEspecialidad());
                        $cita->setConsultorio($slot['office']);
                        $cita->setDoctor($slot['doctor']);
                        $cita->setFecha(clone $currentDate);
                        $cita->setHoraInicio($slot['start']);
                        $cita->setHoraFin($slot['end']);
                        $cita->setSolicitud($request);
                        $cita->setEstadoCita(CitasEstados::EXPECTED);

                        $request->setEstadoSolicitud(CitasSolicitudesEstados::SCHEDULED);

                        $this->em->persist($cita);

                        // Track it so the next request in the loop knows about it
                        $newCitasThisRun[] = $cita;

                        // Remove the request from the pending queue
                        unset($requests[$requestKey]);

                        // Remove the slot so no one else takes it
                        unset($slots[$assignedSlotKey]);

                        $assignedCount++;
                        $dailyAssigned++;
                    }
                }
            }

            if (!empty($requests)) {
                $currentDate->modify('+1 day');
            }
            $daysChecked++;
        }

        $this->em->flush();
        return $assignedCount;
    }

    private function generateSlots(CitasConfiguraciones $config, int $dayOfWeek): array
    {
        $slots = [];
        
        $turnos = $this->em->getRepository(TurnoDoctor::class)->findBy([
            'especialidad' => $config->getEspecialidad(),
            'dayOfWeek' => $dayOfWeek,
            'isActive' => true,
            'status' => $this->em->getRepository(\App\Entity\StatusRecord::class)->getActive()
        ]);
        
        if (empty($turnos)) {
            return $slots;
        }

        $duration = $config->getDuracionCita();
        $receso = $config->isTieneTiempoReceso() ? $config->getTiempoReceso() : 0;
        $totalSlotTime = $duration + $receso;

        foreach ($turnos as $turno) {
            $currentTime = \DateTime::createFromFormat('H:i:s', $turno->getStartTime()->format('H:i:s'));
            $endTime = \DateTime::createFromFormat('H:i:s', $turno->getEndTime()->format('H:i:s'));

            while ($currentTime < $endTime) {
                $slotEnd = (clone $currentTime)->modify("+$duration minutes");

                // No programar si ya no queda tiempo en el turno
                if ($slotEnd > $endTime) break;

                $slots[] = [
                    'start' => clone $currentTime,
                    'end' => clone $slotEnd,
                    'office' => $turno->getConsultorio(),
                    'doctor' => $turno->getDoctor()
                ];
                
                $currentTime->modify("+$totalSlotTime minutes");
            }
        }

        return $slots;
    }

    private function calculateScore(CitasSolicitudes $request, CitasConfiguraciones $config): int
    {
        $score = 0;
        $paciente = $request->getPaciente();
        $age = $paciente->getFechaNacimiento()->diff(new \DateTime())->y;

        if ($config->isTieneEdadPrioridad() && $age >= $config->getEdadPrioridad()) {
            $score += 1000;
        }

        $hoursWaiting = $request->getCreated()->diff(new \DateTimeImmutable())->h;
        $score += $hoursWaiting;

        return $score;
    }

    private function isTimeOverlap(\DateTimeInterface $start1, \DateTimeInterface $end1, \DateTimeInterface $start2, \DateTimeInterface $end2): bool
    {
        $s1 = $start1->format('H:i:s');
        $e1 = $end1->format('H:i:s');
        $s2 = $start2->format('H:i:s');
        $e2 = $end2->format('H:i:s');

        // Two periods overlap if (Start A < End B) and (End A > Start B)
        return ($s1 < $e2) && ($e1 > $s2);
    }

    private function hasConflict(array $slot, $paciente, array $existingCitas, array $newCitasThisRun): bool
    {
        $allCitasForDay = array_merge($existingCitas, $newCitasThisRun);

        foreach ($allCitasForDay as $cita) {
            $overlap = $this->isTimeOverlap($slot['start'], $slot['end'], $cita->getHoraInicio(), $cita->getHoraFin());

            if ($overlap) {
                // 1. Check Doctor Conflict: Is the doctor already booked?
                if ($cita->getDoctor() === $slot['doctor']) {
                    return true;
                }
                // 2. Check Office Conflict: Is the office already booked?
                if ($cita->getConsultorio() === $slot['office']) {
                    return true;
                }
                // 3. Check Patient Conflict: Is the patient already booked elsewhere?
                if ($cita->getPaciente() === $paciente) {
                    return true;
                }
            }
        }
        return false;
    }
}
