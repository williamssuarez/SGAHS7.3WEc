<?php

namespace App\Enum;

/**
 * Enum que representa las condiciones de alta de las emergencias.
 */
enum EmergenciasCondicionAlta: string
{
    case SENT_HOME = 'sent_home';
    case ADMITTED_ROOM = 'admitted_room';
    case TRANSFER = 'transfer';
    case LEFT = 'left';
    case DECEASED = 'deceased';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::SENT_HOME => 'Alta Médica',
            self::ADMITTED_ROOM => 'Hospitalización',
            self::TRANSFER => 'Traslado',
            self::LEFT => 'Fuga/Retiro Voluntario',
            self::DECEASED => 'Fallecimiento',
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
