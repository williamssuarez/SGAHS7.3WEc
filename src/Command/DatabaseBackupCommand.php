<?php

namespace App\Command;

use App\Service\DatabaseBackupService;
use App\Service\AuditService;
use App\Enum\AuditTipos;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:database:backup',
    description: 'Crea un respaldo de la base de datos (apto para tareas periódicas).',
)]
class DatabaseBackupCommand extends Command
{
    public function __construct(
        private DatabaseBackupService $backupService,
        private AuditService $auditService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Iniciando respaldo de base de datos...');

        try {
            $filename = $this->backupService->createBackup();
            
            $this->auditService->persistAndFlushAudit(
                AuditTipos::SYSTEM_DATABASE_BACKUP,
                "Respaldo automático generado vía consola: $filename"
            );

            $io->success("Respaldo completado exitosamente: $filename");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Error al crear el respaldo: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
