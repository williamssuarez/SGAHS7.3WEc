<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de inmunizaciones.
 */
enum InmunizacionesTipos: string
{
    case I_VECTOR_VIRAL = 'vector_viral';
    case I_INACTIVE = 'inactiva';
    case I_TOXOIDE = 'toxoide';
    case I_ARNM = 'arnm';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::I_VECTOR_VIRAL => 'Vector Viral',
            self::I_INACTIVE => 'Inactivada',
            self::I_TOXOIDE => 'Toxoide',
            self::I_ARNM => 'ARNm',
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
