<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de discapacidades.
 */
enum DiscapacidadesTipos: string
{
    case D_PHYSICAL = 'physical';
    case D_SENSORY = 'sensory';
    case D_INTELLECTUAL = 'intellectual';
    case D_ORGANIC = 'organic';
    case D_PSYCHOSOCIAL = 'psychosocial';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::D_PHYSICAL => 'Física',
            self::D_SENSORY => 'Sensorial',
            self::D_INTELLECTUAL => 'Intelectual',
            self::D_ORGANIC => 'Orgánica',
            self::D_PSYCHOSOCIAL => 'Psicosocial',
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
