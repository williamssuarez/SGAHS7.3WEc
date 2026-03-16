<?php

namespace App\Enum;

/**
 * Enum que representa los estados de las camas.
 */
enum CamaEstados: string
{
    case AVAILABLE = 'available';
    case OCUPIED = 'ocupied';
    case CLEANING = 'cleaning';
    case MAINTENANCE = 'maintenance';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::AVAILABLE => 'Disponible',
            self::OCUPIED => 'Ocupada',
            self::CLEANING => 'Limpieza',
            self::MAINTENANCE => 'Mantenimiento',
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
