<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de acciones que audiatar.
 */
enum AuditTipos: string
{
    case CONSULT_VITALS = 'consult_vitals';
    case CONSULT_MEDICATION = 'consult_medication';
    case CONSULT_ALLERGY = 'consult_allergy';
    case CONSULT_CONDITION = 'consult_condition';
    case CONSULT_SICKNESS = 'consult_sickness';
    case CONSULT_DISABILITY = 'consult_disability';
    case CONSULT_FILE = 'consult_file';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::CONSULT_VITALS => 'Vitales',
            self::CONSULT_MEDICATION => 'Medicacion',
            self::CONSULT_ALLERGY => 'Alergia',
            self::CONSULT_CONDITION => 'Condicion',
            self::CONSULT_SICKNESS => 'Enfermedad',
            self::CONSULT_DISABILITY => 'Discapacidad',
            self::CONSULT_FILE => 'Archivo',
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
