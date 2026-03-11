<?php

namespace App\Enum;

/**
 * Enum que representa los tipos de acciones que audiatar.
 */
enum AuditTipos: string
{
    case PATIENT_NEW = 'patient_new';
    case PATIENT_EDIT = 'patient_edit';
    case PATIENT_ALLERGY_NEW = 'patient_allergy_new';
    case PATIENT_ALLERGY_EDIT = 'patient_allergy_edit';
    case PATIENT_FILE_NEW = 'patient_file_new';
    case PATIENT_FILE_DELETE = 'patient_file_delete';
    case CONSULT_VITALS = 'consult_vitals';
    case CONSULT_MEDICATION_NEW = 'consult_medication_new';
    case CONSULT_MEDICATION_EDIT = 'consult_medication_edit';
    case CONSULT_ALLERGY_NEW = 'consult_allergy_new';
    case CONSULT_ALLERGY_EDIT = 'consult_allergy_edit';
    case CONSULT_CONDITION_NEW = 'consult_condition_new';
    case CONSULT_CONDITION_EDIT = 'consult_condition_edit';
    case CONSULT_SICKNESS_NEW = 'consult_sickness_new';
    case CONSULT_SICKNESS_EDIT = 'consult_sickness_edit';
    case CONSULT_DISABILITY_NEW = 'consult_disability_new';
    case CONSULT_DISABILITY_EDIT = 'consult_disability_edit';
    case CONSULT_IMMUNIZATION_NEW = 'consult_immunization_new';
    case CONSULT_IMMUNIZATION_EDIT = 'consult_immunization_edit';
    case CONSULT_FILE_NEW = 'consult_file_new';
    case CONSULT_FILE_DELETE = 'consult_file_delete';
    case CONSULT_PENDING = 'consult_new';
    case CONSULT_ACTIVE = 'consult_active';
    case CONSULT_FINISHED = 'consult_finished';
    case CONSULT_CANCELED = 'consult_canceled';
    case RECEPTION_CHECKIN = 'reception_checkin';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::PATIENT_NEW => 'Registro Nuevo Paciente',
            self::PATIENT_EDIT => 'Edicion de Datos del Paciente',
            self::PATIENT_ALLERGY_NEW => 'Nueva Alergia',
            self::PATIENT_ALLERGY_EDIT => 'Edicion de Alergia',
            self::PATIENT_FILE_NEW => 'Nuevo Archivo',
            self::PATIENT_FILE_DELETE => 'Eliminacion de Archivo',
            self::CONSULT_VITALS => 'Vitales',
            self::CONSULT_MEDICATION_NEW => 'Nueva Prescripcion',
            self::CONSULT_MEDICATION_EDIT => 'Edicion de Prescripcion',
            self::CONSULT_ALLERGY_NEW => 'Nueva Alergia en Consulta',
            self::CONSULT_ALLERGY_EDIT => 'Edicion de Alergia en Consulta',
            self::CONSULT_CONDITION_NEW => 'Nueva Condicion',
            self::CONSULT_CONDITION_EDIT => 'Edicion de Condicion',
            self::CONSULT_SICKNESS_NEW => 'Nueva Enfermedad',
            self::CONSULT_SICKNESS_EDIT => 'Edicion de Enfermedad',
            self::CONSULT_DISABILITY_NEW => 'Nueva Discapacidad',
            self::CONSULT_DISABILITY_EDIT => 'Edicion de Discapacidad',
            self::CONSULT_IMMUNIZATION_NEW => 'Nueva Inmunizacion',
            self::CONSULT_IMMUNIZATION_EDIT => 'Edicion de Inmunizacion',
            self::CONSULT_FILE_NEW => 'Nuevo Archivo en Consulta',
            self::CONSULT_FILE_DELETE => 'Eliminacion de Archivo en Consulta',
            self::CONSULT_PENDING => 'Nueva Consulta Pendiente',
            self::CONSULT_ACTIVE => 'Consulta Iniciada',
            self::CONSULT_FINISHED => 'Consulta Finalizada',
            self::CONSULT_CANCELED => 'Consulta Cancelada',
            self::RECEPTION_CHECKIN => 'Cita Atendida',
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
