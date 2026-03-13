<?php

// src/Service/AppointmentScheduler.php
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

            // PHP's 'N' format returns 1 (Monday) to 7 (Sunday), matching your array perfectly
            $currentDayOfWeek = (int) $currentDate->format('N');

            // Check if the clinic attends this specialty on this day of the week
            if (in_array($currentDayOfWeek, $allowedDays)) {

                $slots = $this->generateSlots($config);
                $dailyAssigned = 0;
                $maxPerDay = $config->getMaxPacientesDia();

                // 4. Fill up the slots for THIS specific day
                while (!empty($slots) && $dailyAssigned < $maxPerDay && !empty($requests)) {

                    // Pop the highest priority request and the next chronological slot
                    $request = array_shift($requests);
                    $slot = array_shift($slots);

                    $cita = new Citas();
                    $cita->setPaciente($request->getPaciente());
                    $cita->setEspecialidad($config->getEspecialidad());
                    $cita->setConsultorio($slot['office']);

                    // Assign to the day the loop is currently on
                    $cita->setFecha(clone $currentDate);
                    $cita->setHoraInicio($slot['start']);
                    $cita->setHoraFin($slot['end']);
                    $cita->setSolicitud($request);
                    $cita->setEstadoCita(CitasEstados::EXPECTED);

                    $request->setEstadoSolicitud(CitasSolicitudesEstados::SCHEDULED);

                    $this->em->persist($cita);
                    $assignedCount++;
                    $dailyAssigned++;
                }
            }

            // If we still have requests left, advance to the next day and try again
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
}
