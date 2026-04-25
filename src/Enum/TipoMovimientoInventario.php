<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de Movimientos.
 */
enum TipoMovimientoInventario: string
{
    case ENTRADA = 'entrada';
    case SALIDA = 'salida';
    case AJUSTE = 'ajuste';
    case EDICION = 'edicion';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::ENTRADA => 'Compra/Recarga',
            self::SALIDA => 'Consumo/Venta',
            self::AJUSTE => 'Ajuste',
            self::EDICION => 'Modificaciones',
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
