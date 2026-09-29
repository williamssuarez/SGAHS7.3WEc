<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NoShiftOverlap extends Constraint
{
    public string $messageDoctor = 'El doctor ya tiene un turno asignado que se solapa en este horario ({{ otherShift }}).';
    public string $messageConsultorio = 'El consultorio ya está ocupado por otro turno en este horario ({{ otherShift }}).';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
