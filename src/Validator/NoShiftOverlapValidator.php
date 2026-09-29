<?php

namespace App\Validator;

use App\Entity\TurnoDoctor;
use App\Repository\TurnoDoctorRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoShiftOverlapValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TurnoDoctorRepository $repo
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoShiftOverlap) {
            throw new \InvalidArgumentException(sprintf('The constraint must be an instance of "%s".', NoShiftOverlap::class));
        }

        if (!$value instanceof TurnoDoctor) {
            throw new UnexpectedTypeException($value, TurnoDoctor::class);
        }

        $doctorId = $value->getDoctor()?->getId();
        $consultorioId = $value->getConsultorio()?->getId();
        $dayOfWeek = $value->getDayOfWeek();
        $startTime = $value->getStartTime();
        $endTime = $value->getEndTime();
        $excludeId = $value->getId();

        if (!$doctorId || !$consultorioId || !$dayOfWeek || !$startTime || !$endTime) {
            // Let the standard NotBlank constraints handle missing data
            return;
        }

        // Buscar posibles conflictos en la BD (solo turnos activos y no eliminados lógicamente)
        $qb = $this->repo->createQueryBuilder('t')
            ->join('t.status', 's')
            ->where('t.dayOfWeek = :day')
            ->andWhere('t.isActive = :active')
            ->andWhere('s.codigo = :statusCode')
            ->setParameter('day', $dayOfWeek)
            ->setParameter('active', true)
            ->setParameter('statusCode', 'ACTRECORD');

        if ($excludeId) {
            $qb->andWhere('t.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $turnos = $qb->getQuery()->getResult();

        foreach ($turnos as $turno) {
            $s1 = $startTime->format('H:i:s');
            $e1 = $endTime->format('H:i:s');
            $s2 = $turno->getStartTime()->format('H:i:s');
            $e2 = $turno->getEndTime()->format('H:i:s');

            if ($s1 < $e2 && $e1 > $s2) {
                $otherShiftInfo = sprintf('%s - %s', $turno->getStartTime()->format('H:i'), $turno->getEndTime()->format('H:i'));

                if ($turno->getDoctor()->getId() === $doctorId) {
                    $this->context->buildViolation($constraint->messageDoctor)
                        ->setParameter('{{ otherShift }}', $otherShiftInfo)
                        ->atPath('doctor')
                        ->addViolation();
                    return; // Prevent adding multiple errors
                }

                if ($turno->getConsultorio()->getId() === $consultorioId) {
                    $this->context->buildViolation($constraint->messageConsultorio)
                        ->setParameter('{{ otherShift }}', $otherShiftInfo)
                        ->atPath('consultorio')
                        ->addViolation();
                    return; // Prevent adding multiple errors
                }
            }
        }
    }
}
