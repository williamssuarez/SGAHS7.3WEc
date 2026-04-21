<?php

namespace App\Enum;

use phpDocumentor\Reflection\Types\Self_;

/**
 * Enum que representa los tipos de acciones que audiatar.
 */
enum AuditTipos: string
{
    case ALL = 'all';
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
    case RECEPTION_CANCELED = 'reception_canceled';
    case SYSTEM_RECEPTION_AUTO_CANCELED = 'system_reception_auto_canceled';
    case SYSTEM_AUTO_LINK_ACCOUNT = 'system_auto_link_account';
    case EMERGENCY_DISCHARGE_TRANSFER = 'emergency_discharge_transfer';
    case EMERGENCY_DISCHARGE_ADMITTED_ROOM = 'emergency_discharge_admitted_room';
    case EMERGENCY_DISCHARGE_DECEASED = 'emergency_discharge_deceased';
    case EMERGENCY_DISCHARGE_SENT_HOME = 'emergency_discharge_sent_home';
    case EMERGENCY_DISCHARGE_LEFT = 'emergency_discharge_left';
    case SURGERY_PROGRAMMED = 'surgery_programmed';
    case SURGERY_PRE_OP = 'surgery_pre_op';
    case SURGERY_TRANS_OP = 'surgery_trans_op';
    case SURGERY_POST_OP = 'surgery_post_op';
    case SURGERY_FINISHED = 'surgery_finished';
    case SURGERY_CANCELED = 'surgery_cancelled';

    /**
     * Retorna un texto amigable para el usuario final.
     */
    public function getReadableText(): string
    {
        return match($this) {
            self::ALL => 'Todos',
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
            self::RECEPTION_CANCELED => 'Cita Cancelada',
            self::SYSTEM_RECEPTION_AUTO_CANCELED => 'Cancelación de cita automática',
            self::SYSTEM_AUTO_LINK_ACCOUNT => 'Vinculacion automática de cuenta de usuario con perfil de paciente',
            self::EMERGENCY_DISCHARGE_TRANSFER => 'Alta de emergencia: Traslado',
            self::EMERGENCY_DISCHARGE_ADMITTED_ROOM => 'Alta de emergencia: Hospitalización',
            self::EMERGENCY_DISCHARGE_DECEASED => 'Alta de emergencia: Fallecimiento',
            self::EMERGENCY_DISCHARGE_SENT_HOME => 'Alta de emergencia: Alta Médica',
            self::EMERGENCY_DISCHARGE_LEFT => 'Alta de emergencia: Fuga/Retiro Voluntario',
            self::SURGERY_PROGRAMMED => 'Cirugia Programada',
            self::SURGERY_PRE_OP => 'Cirugia en Pre-Operatorio',
            self::SURGERY_TRANS_OP => 'Cirugia en Trans-Operatorio',
            self::SURGERY_POST_OP => 'Cirugia en Post-Operatorio',
            self::SURGERY_FINISHED => 'Cirugia Finalizada',
            self::SURGERY_CANCELED => 'Cirugia Cancelada',
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
