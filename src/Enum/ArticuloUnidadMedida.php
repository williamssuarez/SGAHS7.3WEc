<?php

namespace App\Enum;

/**
 * Enum que representa las unidades de medida de los Articulos.
 */
enum ArticuloUnidadMedida: string
{
    case A_CAJA = 'caja';
    case A_VIAL = 'vial';
    case A_UNIDAD = 'unidad';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::A_CAJA => 'Caja',
            self::A_VIAL => 'Vial',
            self::A_UNIDAD => 'Unidad',
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
