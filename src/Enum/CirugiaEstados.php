<?php

// src/Enum/CirugiaEstados.php
namespace App\Enum;

enum CirugiaEstados: string
{
    case PROGRAMADA = 'programada'; // En agenda
    case PRE_OP = 'pre_op';         // Preparación / Anestesia
    case TRANS_OP = 'trans_op';     // Cirugía en curso
    case POST_OP = 'post_op';       // URPA / Recuperación
    case FINALIZADA = 'finalizada'; // Trasladado a piso o alta
    case CANCELADA = 'cancelada';   // Suspendida

    public function getReadableText(): string
    {
        return match($this) {
            self::PROGRAMADA => 'Programada',
            self::PRE_OP => 'Pre-Operatorio',
            self::TRANS_OP => 'Trans-Operatorio',
            self::POST_OP => 'Recuperación (URPA)',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
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
