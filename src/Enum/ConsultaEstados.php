<?php

namespace App\Enum;

/**
 * Enum que representa los estados de consulta medica.
 */
enum ConsultaEstados: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case CANCELED = 'canceled';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::ACTIVE => 'En Progreso',
            self::FINISHED => 'Finalizada',
            self::CANCELED => 'Cancelada',
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
