<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BackupSchedulerConfig
{
    private string $configFilePath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->configFilePath = $params->get('kernel.project_dir') . '/var/backup_schedule.json';
    }

    public function getConfig(): array
    {
        if (!file_exists($this->configFilePath)) {
            return $this->getDefaultConfig();
        }

        $content = file_get_contents($this->configFilePath);
        $data = json_decode($content, true);

        return is_array($data) ? array_merge($this->getDefaultConfig(), $data) : $this->getDefaultConfig();
    }

    public function saveConfig(array $config): void
    {
        $currentConfig = $this->getConfig();
        
        // Preserve last_run when saving config
        if (isset($currentConfig['last_run']) && !isset($config['last_run'])) {
            $config['last_run'] = $currentConfig['last_run'];
        }

        file_put_contents($this->configFilePath, json_encode($config, JSON_PRETTY_PRINT));
    }

    public function markAsRun(): void
    {
        $config = $this->getConfig();
        $config['last_run'] = (new \DateTimeImmutable())->format('c');
        $this->saveConfig($config);
    }

    private function getDefaultConfig(): array
    {
        return [
            'enabled' => false,
            'frequency' => 'weekly', // daily, weekly, monthly
            'day_of_week' => 6, // 1 (Monday) to 7 (Sunday). 6 is Saturday
            'day_of_month' => 1, // 1 to 31
            'hour' => 23, // 0 to 23
            'minute' => 0, // 0 to 59
            'last_run' => null,
        ];
    }
}
