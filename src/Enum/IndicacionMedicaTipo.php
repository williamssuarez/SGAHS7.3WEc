<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de consulta medica.
 */
enum IndicacionMedicaTipo: string
{
    case MEDICINE = 'medicine';
    case DIET = 'diet';
    case GENERAL_WATCH = 'general_watch';
    case EXAM = 'exam';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::MEDICINE => 'Medicamento',
            self::DIET => 'Dieta',
            self::GENERAL_WATCH => 'Cuidado General / Enfermería',
            self::EXAM => 'Laboratorio / Imagenología',
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
