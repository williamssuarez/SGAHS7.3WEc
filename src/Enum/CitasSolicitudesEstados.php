<?php

namespace App\Enum;

/**
 * Enum que representa los estados de las solicitudes de citas.
 */
enum CitasSolicitudesEstados: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SCHEDULED = 'finished';
    case REJECTED = 'canceled';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSING => 'Procesando',
            self::SCHEDULED => 'Programada',
            self::REJECTED => 'Rechazada',
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
