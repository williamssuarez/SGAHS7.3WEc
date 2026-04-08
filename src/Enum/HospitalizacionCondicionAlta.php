<?php

namespace App\Enum;

/**
 * Enum que representa las condiciones de alta de las emergencias.
 */
enum HospitalizacionCondicionAlta: string
{
    case SENT_HOME = 'sent_home';
    case DECEASED = 'deceased';
    case TRANSFER = 'transfer';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::SENT_HOME => 'Alta Médica',
            self::DECEASED => 'Fallecimiento',
            self::TRANSFER => 'Traslado',
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
