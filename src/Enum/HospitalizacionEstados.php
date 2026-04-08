<?php

namespace App\Enum;

/**
 * Enum que representa los estados de las camas.
 */
enum HospitalizacionEstados: string
{
    case PENDING_BED = 'pending_bed';
    case ADMITTED = 'admitted';
    case DISCHARGED = 'discharged';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PENDING_BED => 'Pendiente',
            self::ADMITTED => 'Admitido',
            self::DISCHARGED => 'Alta',
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
