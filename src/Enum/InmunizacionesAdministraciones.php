<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de administraciones.
 */
enum InmunizacionesAdministraciones: string
{
    case IM = 'im';
    case SC = 'sc';
    case IDE = 'ide';
    case ORA = 'ora';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::IM => 'Intramuscular',
            self::SC => 'Subcutánea',
            self::IDE => 'Intradérmica',
            self::ORA => 'Oral',
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
