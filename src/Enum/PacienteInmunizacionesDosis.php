<?php

namespace App\Enum;

/**
 * Enum que representa las dosis de las inmunizaciones de los pacientes.
 */
enum PacienteInmunizacionesDosis: string
{
    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';
    case BOOSTER = 'booster';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::FIRST => 'Primera Dosis',
            self::SECOND => 'Segunda Dosis',
            self::THIRD => 'Tercera Dosis',
            self::BOOSTER => 'Refuerzo',
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
