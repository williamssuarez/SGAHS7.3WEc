<?php

namespace App\Enum;

/**
 * Enum que representa las categorias de enfermedades.
 */
enum EnfermedadesCategorias: string
{
    case E_MENTAL = 'mental';
    case E_TRANSMITTABLE = 'transmittable';
    case E_GENETIC = 'genetic';
    case E_NUTRITIONAL = 'nutritional';
    case E_ONCOLOGICAL = 'oncological';
    case E_CHRONIC = 'chronic';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::E_MENTAL => 'Mental',
            self::E_TRANSMITTABLE => 'Transmisible',
            self::E_GENETIC => 'Genética',
            self::E_NUTRITIONAL => 'Nutricional',
            self::E_ONCOLOGICAL => 'Oncológica',
            self::E_CHRONIC => 'Cronica',
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
