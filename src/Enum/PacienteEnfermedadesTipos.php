<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de diagnostico de enfermedad del paciente.
 */
enum PacienteEnfermedadesTipos: string
{
    case PRESUMPTIVE = 'presumptive';
    case DEFINITIVE = 'definitive';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PRESUMPTIVE => 'Presuntivo',
            self::DEFINITIVE => 'Definitivo',
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
