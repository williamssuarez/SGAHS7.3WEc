<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class OneActiveConfigPerSpecialty extends Constraint
{
    public string $message = 'Ya existe una configuración activa para esta especialidad. Debes desactivarla primero.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
