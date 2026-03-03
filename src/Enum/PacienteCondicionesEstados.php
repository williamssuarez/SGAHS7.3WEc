<?php

namespace App\Enum;

/**
 * Enum que representa los estados de la condicion del paciente.
 */
enum PacienteCondicionesEstados: string
{
    case ACTIVE = 'active';
    case CHRONIC = 'chronic';
    case RESOLVED = 'resolved';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::ACTIVE => 'Activa',
            self::CHRONIC => 'Cronica',
            self::RESOLVED => 'Resuelta',
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
