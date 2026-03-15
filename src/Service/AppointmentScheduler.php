<?php

namespace App\Service;

use App\Entity\CitasSolicitudes;
use App\Entity\Citas;
use App\Entity\CitasConfiguraciones;
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

        // Fetch the allowed days (e.g., [1, 5] for Monday and Friday)
        $allowedDays = $config->getDiasSemana();
        if (empty($allowedDays)) return 0; // Failsafe if the config is broken

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

            if (in_array($currentDayOfWeek, $allowedDays)) {

                // NEW: Fetch all existing appointments for this specific day from the DB
                // This prevents double-booking from previous cron runs and checks other specialties
                $existingCitas = $this->em->getRepository(Citas::class)->findBy([
                    'fecha' => $currentDate,
                    'estadoCita' => CitasEstados::EXPECTED // Only check against active appointments
                ]);

                $slots = $this->generateSlots($config);
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

    private function generateSlots(CitasConfiguraciones $config): array
    {
        $slots = [];
        $currentTime = \DateTime::createFromFormat('H:i:s', $config->getHoraInicio()->format('H:i:s'));
        $endTime = \DateTime::createFromFormat('H:i:s', $config->getHoraFin()->format('H:i:s'));

        $duration = $config->getDuracionCita();
        $receso = $config->isTieneTiempoReceso() ? $config->getTiempoReceso() : 0;
        $totalSlotTime = $duration + $receso;

        while ($currentTime < $endTime) {
            $slotEnd = (clone $currentTime)->modify("+$duration minutes");

            // No programar si ya no queda tiempo en el dia
            if ($slotEnd > $endTime) break;

            foreach ($config->getConsultorio() as $office) {
                $slots[] = [
                    'start' => clone $currentTime,
                    'end' => clone $slotEnd,
                    'office' => $office
                ];
            }
            $currentTime->modify("+$totalSlotTime minutes");
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
                // 1. Check Office Conflict: Is the office already booked at this time?
                if ($cita->getConsultorio() === $slot['office']) {
                    return true;
                }
                // 2. Check Patient Conflict: Is the patient already booked elsewhere at this time?
                if ($cita->getPaciente() === $paciente) {
                    return true;
                }
            }
        }
        return false;
    }
}
