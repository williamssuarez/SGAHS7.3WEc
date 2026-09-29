<?php

namespace App\Validator;

use App\Entity\CitasConfiguraciones;
use App\Entity\StatusRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OneActiveConfigPerSpecialtyValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OneActiveConfigPerSpecialty) {
            throw new UnexpectedTypeException($constraint, OneActiveConfigPerSpecialty::class);
        }

        if (!$value instanceof CitasConfiguraciones) {
            throw new UnexpectedTypeException($value, CitasConfiguraciones::class);
        }

        if (!$value->isActive() || !$value->getEspecialidad()) {
            return;
        }

        $repo = $this->em->getRepository(CitasConfiguraciones::class);
        $statusActive = $this->em->getRepository(StatusRecord::class)->getActive();

        $qb = $repo->createQueryBuilder('c')
            ->where('c.especialidad = :especialidad')
            ->andWhere('c.isActive = :active')
            ->andWhere('c.status = :status')
            ->setParameter('especialidad', $value->getEspecialidad())
            ->setParameter('active', true)
            ->setParameter('status', $statusActive);

        if ($value->getId()) {
            $qb->andWhere('c.id != :id')
               ->setParameter('id', $value->getId());
        }

        $existing = $qb->getQuery()->getOneOrNullResult();

        if ($existing) {
            $this->context->buildViolation($constraint->message)
                ->atPath('isActive')
                ->addViolation();
        }
    }
}
