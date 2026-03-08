<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de administraciones.
 */
enum PrescripcionesRutas: string
{
    case ORA = 'ora';
    case IV = 'iv';
    case IM = 'im';
    case SC = 'sc';
    case IH = 'ih';
    case INA = 'ina';
    case L_EAR = 'l_ear';
    case R_EAR = 'r_ear';
    case B_EARS = 'b_ears';
    case L_EYE = 'l_eye';
    case R_EYE = 'r_eye';
    case B_EYES = 'b_eyes';
    case TR = 'tr';
    case VG = 'vg';
    case RT = 'rt';
    case SN = 'sn';
    case IO = 'io';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::ORA => 'Oral',
            self::IV => 'Intravenosa',
            self::IM => 'Intramuscular',
            self::SC => 'Subcutánea',
            self::IH => 'Inhalación',
            self::INA => 'Intranasal',
            self::L_EAR => 'Oído izquierdo',
            self::R_EAR => 'Oído derecho',
            self::B_EARS => 'Ambos oídos',
            self::L_EYE => 'Ojo izquierdo',
            self::R_EYE => 'Ojo derecho',
            self::B_EYES => 'Ambos ojos',
            self::TR => 'Transdérmica',
            self::VG => 'Vaginal',
            self::RT => 'Rectal',
            self::SN => 'Sonda nasogástrica',
            self::IO => 'Intraósea',
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
