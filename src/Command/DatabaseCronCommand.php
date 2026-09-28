<?php

namespace App\Command;

use App\Service\DatabaseBackupService;
use App\Service\AuditService;
use App\Service\BackupSchedulerConfig;
use App\Enum\AuditTipos;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:database:cron',
    description: 'Verifica y ejecuta respaldos automáticos programados si es el momento adecuado.',
)]
class DatabaseCronCommand extends Command
{
    public function __construct(
        private DatabaseBackupService $backupService,
        private AuditService $auditService,
        private BackupSchedulerConfig $configurator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $config = $this->configurator->getConfig();

        if (empty($config['enabled'])) {
            $io->note('Los respaldos automáticos están deshabilitados en la configuración.');
            return Command::SUCCESS;
        }

        $timezone = new \DateTimeZone('America/Caracas');
        $now = new \DateTimeImmutable('now', $timezone);
        
        // Evitar que se ejecute múltiples veces el mismo día
        if (!empty($config['last_run'])) {
            $lastRun = (new \DateTimeImmutable($config['last_run']))->setTimezone($timezone);
            if ($lastRun->format('Y-m-d') === $now->format('Y-m-d')) {
                $io->note('El respaldo ya se ejecutó el día de hoy. Ignorando.');
                return Command::SUCCESS;
            }
        }

        $shouldRun = false;
        $currentHour = (int) $now->format('G'); // 0-23
        $scheduledHour = (int) $config['hour'];

        if ($currentHour === $scheduledHour) {
            switch ($config['frequency']) {
                case 'daily':
                    $shouldRun = true;
                    break;
                case 'weekly':
                    $currentDayOfWeek = (int) $now->format('N'); // 1 (Mon) - 7 (Sun)
                    if ($currentDayOfWeek === (int) $config['day_of_week']) {
                        $shouldRun = true;
                    }
                    break;
                case 'monthly':
                    $currentDayOfMonth = (int) $now->format('j'); // 1 - 31
                    if ($currentDayOfMonth === (int) $config['day_of_month']) {
                        $shouldRun = true;
                    }
                    break;
            }
        }

        if (!$shouldRun) {
            $io->note('No es el momento programado para el respaldo automático.');
            return Command::SUCCESS;
        }

        $io->title('Iniciando respaldo automático programado...');

        try {
            $filename = $this->backupService->createBackup();
            
            // Registrar auditoría como automático
            $this->auditService->persistAndFlushAudit(
                AuditTipos::SYSTEM_DATABASE_BACKUP_AUTO,
                "Respaldo automático programado generado exitosamente: $filename"
            );

            // Actualizar la fecha de última ejecución
            $this->configurator->markAsRun();

            $io->success("Respaldo completado exitosamente: $filename");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Error al crear el respaldo automático: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
