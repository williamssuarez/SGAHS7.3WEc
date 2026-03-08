<?php

namespace App\Enum;

/**
 * Enum que representa los estados de la prescripcion de medicamentos.
 */
enum PrescripcionesEstados: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspendida';
    case FINISHED = 'finished';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::ACTIVE => 'Activa',
            self::SUSPENDED => 'Suspendida',
            self::FINISHED => 'Finalizada',
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
