<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de sangre.
 */
enum SangreTipos: string
{
    case POSITIVE_O = 'positive_o';
    case NEGATIVE_O = 'negative_o';
    case POSITIVE_A = 'positive_a';
    case NEGATIVE_A = 'negative_a';
    case POSITIVE_B = 'positive_b';
    case NEGATIVE_B = 'negative_b';
    case POSITIVE_AB = 'positive_ab';
    case NEGATIVE_AB = 'negative_ab';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::POSITIVE_O => 'O+',
            self::NEGATIVE_O => 'O-',
            self::POSITIVE_A => 'A+',
            self::NEGATIVE_A => 'A-',
            self::POSITIVE_B => 'B+',
            self::NEGATIVE_B => 'B-',
            self::POSITIVE_AB => 'AB+',
            self::NEGATIVE_AB => 'AB-',
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
