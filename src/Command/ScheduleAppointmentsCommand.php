<?php

namespace App\Command;

use App\Entity\CitasConfiguraciones;
use App\Repository\CitasConfiguracionesRepository;
use App\Repository\StatusRecordRepository;
use App\Service\AppointmentScheduler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:schedule-appointments',
    description: 'Ejecuta el motor de asignación de citas basado en las configuraciones activas.',
)]
class ScheduleAppointmentsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface                  $em,
        private readonly AppointmentScheduler           $scheduler,
        private readonly CitasConfiguracionesRepository $configRepo,
        private readonly StatusRecordRepository         $statusRepo
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando Motor de Asignación de Citas');

        // 1. Get Active Configurations
        $activeStatus = $this->statusRepo->getActive();
        $configs = $this->configRepo->findBy(['status' => $activeStatus]);

        if (empty($configs)) {
            $io->warning('No se encontraron configuraciones de citas activas.');
            return Command::SUCCESS;
        }

        $targetDate = new \DateTime('+10 day');
        $totalAssigned = 0;

        foreach ($configs as $config) {
            $specialtyName = $config->getEspecialidad()->getNombre();
            $io->text("Procesando cola para: <info>$specialtyName</info>...");

            try {
                $assigned = $this->scheduler->processQueue($config, $targetDate);
                $totalAssigned += $assigned;

                if ($assigned > 0) {
                    $io->note("Asignadas $assigned citas para $specialtyName.");
                }
            } catch (\Exception $e) {
                $io->error("Error procesando $specialtyName: " . $e->getMessage());
            }
        }

        if ($totalAssigned > 0) {
            $io->success("¡Proceso completado! Se asignaron un total de $totalAssigned citas.");
        } else {
            $io->info('El motor terminó sin asignar nuevas citas (sin solicitudes o sin cupos).');
        }

        return Command::SUCCESS;
    }
}
