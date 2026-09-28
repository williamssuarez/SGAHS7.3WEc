<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class DatabaseBackupService
{
    private string $backupDir;
    private string $databaseUrl;
    private string $backupStrategy;
    
    public function __construct(
        private ParameterBagInterface $params,
        private EntityManagerInterface $em
    ) {
        $this->backupDir = $this->params->get('kernel.project_dir') . '/var/backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0777, true);
        }
        $this->databaseUrl = $_ENV['DATABASE_URL'] ?? '';
        $this->backupStrategy = $_ENV['BACKUP_STRATEGY'] ?? 'docker';
    }

    private function enableMaintenance(): void
    {
        $flagPath = $this->params->get('kernel.project_dir') . '/var/maintenance.flag';
        touch($flagPath);
    }

    private function disableMaintenance(): void
    {
        $flagPath = $this->params->get('kernel.project_dir') . '/var/maintenance.flag';
        if (file_exists($flagPath)) {
            unlink($flagPath);
        }
    }

    public function syncSequences(): void
    {
        $conn = $this->em->getConnection();
        $sql = "
            DO $$
            DECLARE
                seq_record RECORD;
            BEGIN
                FOR seq_record IN 
                    SELECT seq.relname AS seq_name,
                           tab.relname AS tab_name,
                           col.attname AS col_name
                    FROM pg_class seq
                    JOIN pg_depend dep ON dep.objid = seq.oid
                    JOIN pg_class tab ON dep.refobjid = tab.oid
                    JOIN pg_attribute col ON col.attnum = dep.refobjsubid AND col.attrelid = tab.oid
                    WHERE seq.relkind = 'S'
                LOOP
                    EXECUTE 'SELECT setval(' || quote_literal(seq_record.seq_name) || ', COALESCE((SELECT MAX(' || quote_ident(seq_record.col_name) || ') FROM ' || quote_ident(seq_record.tab_name) || '), 1), true)';
                END LOOP;
            END $$;
        ";
        try {
            $conn->executeStatement($sql);
        } catch (\Exception $e) {
            // Ignorar errores si no aplica a alguna tabla
        }
    }

    private function getDbConfig(): array
    {
        $parsed = parse_url($this->databaseUrl);
        return [
            'user' => $parsed['user'] ?? '',
            'pass' => $parsed['pass'] ?? '',
            'host' => $parsed['host'] ?? '',
            'port' => $parsed['port'] ?? 5432,
            'dbname' => ltrim($parsed['path'] ?? '', '/'),
        ];
    }

    public function createBackup(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->enableMaintenance();
        try {
            $this->syncSequences(); // Sincronizar antes del respaldo para curar la BD si estaba corrupta

            $config = $this->getDbConfig();
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupDir . '/' . $filename;
            
            if ($this->backupStrategy === 'docker') {
                $command = sprintf(
                    'docker compose exec -T -e PGPASSWORD="%s" database pg_dump -U %s -c --if-exists -d %s',
                    $config['pass'], $config['user'], $config['dbname']
                );
            } else {
                $command = sprintf(
                    'pg_dump -U %s -h %s -p %s -c --if-exists -d %s',
                    $config['user'], $config['host'], $config['port'], $config['dbname']
                );
            }

            $process = Process::fromShellCommandline($command, $this->params->get('kernel.project_dir'));
            
            if ($this->backupStrategy === 'binary') {
                $process->setEnv(['PGPASSWORD' => $config['pass']]);
            }
            
            $process->setTimeout(600); // 10 minutes

            $fp = fopen($filepath, 'w');
            $process->run(function ($type, $buffer) use ($fp) {
                if (Process::OUT === $type) {
                    fwrite($fp, $buffer);
                }
            });

            if (!$process->isSuccessful()) {
                fclose($fp);
                unlink($filepath); // clean up
                throw new ProcessFailedException($process);
            }
            fclose($fp);

            return $filename;
        } finally {
            $this->disableMaintenance();
        }
    }

    public function importBackup(string $filename): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->enableMaintenance();
        try {
            $filepath = $this->backupDir . '/' . $filename;
            if (!file_exists($filepath)) {
                throw new RuntimeException("El archivo de respaldo no existe.");
            }

            $config = $this->getDbConfig();

            // 1. Limpiar completamente la base de datos actual antes de importar
            $dropProcess = Process::fromShellCommandline('php bin/console doctrine:schema:drop --force --full-database', $this->params->get('kernel.project_dir'));
            $dropProcess->run();
            if (!$dropProcess->isSuccessful()) {
                throw new ProcessFailedException($dropProcess);
            }

            // 2. Importar el respaldo
            if ($this->backupStrategy === 'docker') {
                $command = sprintf(
                    'docker compose exec -T -e PGPASSWORD="%s" database psql -v ON_ERROR_STOP=1 -U %s -d %s',
                    $config['pass'], $config['user'], $config['dbname']
                );
            } else {
                $command = sprintf(
                    'psql -v ON_ERROR_STOP=1 -U %s -h %s -p %s -d %s',
                    $config['user'], $config['host'], $config['port'], $config['dbname']
                );
            }
            
            $process = Process::fromShellCommandline($command, $this->params->get('kernel.project_dir'));

            if ($this->backupStrategy === 'binary') {
                $process->setEnv(['PGPASSWORD' => $config['pass']]);
            }
            
            $process->setInput(fopen($filepath, 'r'));
            $process->setTimeout(1200); // 20 minutes
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $this->syncSequences(); // Sincronizar después de restaurar para prevenir corrupción
        } finally {
            $this->disableMaintenance();
        }
    }

    public function deleteBackup(string $filename): void
    {
        $filepath = $this->backupDir . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function listBackups(): array
    {
        $files = [];
        if (is_dir($this->backupDir)) {
            foreach (scandir($this->backupDir) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($this->backupDir . '/' . $file),
                        'date' => filemtime($this->backupDir . '/' . $file)
                    ];
                }
            }
        }
        
        // Sort by date descending
        usort($files, fn($a, $b) => $b['date'] <=> $a['date']);
        
        return $files;
    }
}
