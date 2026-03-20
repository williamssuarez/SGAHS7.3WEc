<?php

namespace App\Enum;

/**
 * Enum que representa los estados de las emergencias.
 */
enum EmergenciasEstados: string
{
    case WAITING_TRIAGE = 'waiting_triage';
    case WAITING_BED = 'waiting_bed';
    case IN_TREATMENT = 'in_treatment';
    case DISCHARGED = 'discharged';
    case DERIVED_CONSULTATION = 'derived_consultation';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::WAITING_TRIAGE => 'En espera de Triaje',
            self::WAITING_BED => 'En espera de Cama',
            self::IN_TREATMENT => 'En Cama',
            self::DISCHARGED => 'Alta médica',
            self::DERIVED_CONSULTATION => 'Enviado a Consulta',
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
