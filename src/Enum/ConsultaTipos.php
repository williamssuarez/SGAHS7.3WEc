<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de consulta medica.
 */
enum ConsultaTipos: string
{
    case CT_GENERAL = 'atencion_general';
    case CT_ESPECIALIDAD = 'atencion_especialidad';
    case CT_SEGUIMIENTO = 'atencion_seguimiento';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::CT_GENERAL => 'Atención Médica General',
            self::CT_ESPECIALIDAD => 'Consulta con Especialista',
            self::CT_SEGUIMIENTO => 'Cita de Control y Seguimiento',
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
