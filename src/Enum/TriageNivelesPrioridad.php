<?php

namespace App\Enum;

/**
 * Enum que representa los niveles de prioridad para el triage.
 */
enum TriageNivelesPrioridad: string
{
    case LEVEL_1 = 'level_1';
    case LEVEL_2 = 'level_2';
    case LEVEL_3 = 'level_3';
    case LEVEL_4 = 'level_4';
    case LEVEL_5 = 'level_5';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::LEVEL_1 => '1 - Resucitación (Atención Inmediata)',
            self::LEVEL_2 => '2 - Emergencia (10-15 min)',
            self::LEVEL_3 => '3 - Urgencia (60 min)',
            self::LEVEL_4 => '4 - Prioridad Menor (120 min)',
            self::LEVEL_5 => '5 - No Urgente (240 min)',
        };
    }

    /**
     * Retorna todos los posibles valores como array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
