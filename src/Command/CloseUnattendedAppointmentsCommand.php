<?php

namespace App\Command;

use App\Service\AppointmentCleaner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:close-unattended',
    description: 'Cierra automáticamente las citas que quedaron como pendientes al finalizar el día.',
)]
class CloseUnattendedAppointmentsCommand extends Command
{
    public function __construct(
        private readonly AppointmentCleaner $cleaner
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('yesterday', 'y', InputOption::VALUE_NONE, 'Ejecuta la limpieza para el día de ayer en lugar de hoy.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando Limpieza de Citas No Atendidas');

        // Determine which day to clean based on the option
        $targetDate = $input->getOption('yesterday') ? new \DateTime('yesterday') : new \DateTime('today');

        $io->text("Buscando citas abandonadas para el " . $targetDate->format('d/m/Y') . "...");

        try {
            $closedCount = $this->cleaner->closeUnattendedAppointments($targetDate);

            if ($closedCount > 0) {
                $io->success("¡Limpieza completada! Se marcaron $closedCount citas como no atendidas.");
            } else {
                $io->info('Excelente: No quedaron citas pendientes para este día.');
            }
        } catch (\Exception $e) {
            $io->error("Error crítico durante la limpieza: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
