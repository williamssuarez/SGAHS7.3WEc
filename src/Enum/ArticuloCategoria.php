<?php

namespace App\Enum;

/**
 * Enum que representa las categorias de Articulos.
 */
enum ArticuloCategoria: string
{
    case A_MEDICINE = 'medicine';
    case A_SURGICAL_MATERIAL = 'surgical_material';
    case A_GENERAL_SUPPLY = 'general_supply';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::A_MEDICINE => 'Medicamento',
            self::A_SURGICAL_MATERIAL => 'Material Médico-Quirúrgico',
            self::A_GENERAL_SUPPLY => 'Insumo General',
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
