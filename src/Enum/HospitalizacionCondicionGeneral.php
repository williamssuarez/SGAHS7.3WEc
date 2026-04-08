<?php

namespace App\Enum;

/**
 * Enum que representa las condiciones de alta de las emergencias.
 */
enum HospitalizacionCondicionGeneral: string
{
    case STABLE = 'stable';
    case DELICATE = 'delicate';
    case SEVERE = 'severe';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::STABLE => 'Estable',
            self::DELICATE => 'Delicado',
            self::SEVERE => 'Grave',
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
