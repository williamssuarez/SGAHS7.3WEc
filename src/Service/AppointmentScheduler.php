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

    public function processQueue(CitasConfiguraciones $config, \DateTime $targetDate): int
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
        usort($requests, fn($a, $b) => $b->getScorePrioridad() <=> $a->getScorePrioridad());

        // 3. Generate available slots based on config
        $slots = $this->generateSlots($config);
        $assignedCount = 0;

        // 4. Match requests to slots
        foreach ($requests as $request) {
            if (empty($slots) || $assignedCount >= $config->getMaxPacientesDia()) {
                break; // No more slots or reached daily limit
            }

            // Pop the next available slot from the pool
            $slot = array_shift($slots);

            $cita = new Citas();
            $cita->setPaciente($request->getPaciente());
            $cita->setEspecialidad($config->getEspecialidad());
            $cita->setConsultorio($slot['office']);
            $cita->setFecha($targetDate);
            $cita->setHoraInicio($slot['start']);
            $cita->setHoraFin($slot['end']);
            $cita->setSolicitud($request);
            $cita->setEstadoCita(CitasEstados::EXPECTED);

            $request->setEstadoSolicitud(CitasSolicitudesEstados::SCHEDULED); // Update to match your Enum

            $this->em->persist($cita);
            $assignedCount++;
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

            // Don't schedule if the appointment runs past closing time
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
