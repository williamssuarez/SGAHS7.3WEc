<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de dosis para los medicamentos.
 */
enum MedicamentosDosisTipos: string
{
    case MED_AMPOLLA = 'med_amp';
    case MED_APPLICACION = 'med_app';
    case MED_BARRA = 'med_barra';
    case MED_CAPSULA = 'med_capsula';
    case MED_COMPRESA = 'med_compresa';
    case MED_CUCHARADA = 'med_cucharada';
    case MED_CUCHARADITA = 'med_cucharadita';
    case MED_ENVASE = 'med_envase';
    case MED_ENEMA = 'med_enem';
    case MED_GOTA = 'med_gota';
    case MED_GOTA_METRICA = 'med_gota_metrica';
    case MED_JARABE = 'med_jarabe';
    case MED_JERINGA = 'med_jeringa';
    case MED_KILOGRAMO = 'med_kilogramo';
    case MED_LATA = 'med_lata';
    case MED_LITRO = 'med_litro';
    case MED_MILIEQUIVALENTE = 'med_miliequivalente';
    case MED_MICROGRAMO = 'med_microgramo';
    case MED_MILIGRAMO = 'med_miligramo';
    case MED_MILILITRO = 'med_mililitro';
    case MED_MILLONES_UNIDADES = 'med_millones_unidades';
    case MED_NEBULIZACION = 'med_nebu';
    case MED_OBLEA = 'med_oblea';
    case MED_ONZA_LIQUIDA = 'med_onza';
    case MED_PARCHE = 'med_parche';
    case MED_PINTA = 'med_pinta';
    case MED_PUFF = 'med_puff';
    case MED_SPRAY = 'med_spray';
    case MED_SUPOSITORIO = 'med_supositorio';
    case MED_TABLETA = 'med_tablet';
    case MED_TROCISCO = 'med_troci';
    case MED_TUBO = 'med_tubo';
    case MED_UNIDAD = 'med_unidad';
    case MED_VIAL = 'med_vial';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::MED_AMPOLLA => 'Ampolla',
            self::MED_APPLICACION => 'Aplicacion',
            self::MED_BARRA => 'Barra',
            self::MED_CAPSULA => 'Capsula',
            self::MED_COMPRESA => 'Compresa',
            self::MED_CUCHARADA => 'Cucharada',
            self::MED_CUCHARADITA => 'Cucharadita',
            self::MED_ENVASE => 'Envase',
            self::MED_ENEMA => 'Enema',
            self::MED_GOTA => 'Gota',
            self::MED_GOTA_METRICA => 'Gota Metrica',
            self::MED_JARABE => 'Jarabe',
            self::MED_JERINGA => 'Jeringa',
            self::MED_KILOGRAMO => 'Kilogramo',
            self::MED_LATA => 'Lata',
            self::MED_LITRO => 'Litro',
            self::MED_MILIEQUIVALENTE => 'Miliequivalente',
            self::MED_MICROGRAMO => 'Microgramo',
            self::MED_MILIGRAMO => 'Miligramo',
            self::MED_MILILITRO => 'Mililitro',
            self::MED_MILLONES_UNIDADES => 'Millones de Unidades',
            self::MED_NEBULIZACION => 'Nebulizacion',
            self::MED_OBLEA => 'Oblea',
            self::MED_ONZA_LIQUIDA => 'Onza Liquida',
            self::MED_PARCHE => 'Parche',
            self::MED_PINTA => 'Pinta',
            self::MED_PUFF => 'Puff',
            self::MED_SPRAY => 'Spray',
            self::MED_SUPOSITORIO => 'Supositorio',
            self::MED_TABLETA => 'Tableta',
            self::MED_TROCISCO => 'Trocisco',
            self::MED_TUBO => 'Tubo',
            self::MED_UNIDAD => 'Unidad',
            self::MED_VIAL => 'Vial',
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
