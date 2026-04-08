<?php

namespace App\Enum;

/**
 * Enum que representa los estados de consulta medica.
 */
enum IndicacionMedicaEstado: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case SUSPENDED = 'suspended';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::ACTIVE => 'En Progreso',
            self::FINISHED => 'Finalizada',
            self::SUSPENDED => 'Suspendida',
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
