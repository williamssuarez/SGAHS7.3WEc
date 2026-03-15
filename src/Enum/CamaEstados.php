<?php

namespace App\Enum;

/**
 * Enum que representa los estados de las camas.
 */
enum CamaEstados: string
{
    case EXPECTED = 'expected';
    case CHECKED_IN = 'checked_in';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::EXPECTED => 'Esperado',
            self::CHECKED_IN => 'Atendido',
            self::COMPLETED => 'Completado',
            self::CANCELED => 'Cancelado',
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
