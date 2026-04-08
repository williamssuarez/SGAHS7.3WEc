<?php

// src/Form/HorarioVisitasType.php
namespace App\Form;

use App\Entity\Area;
use App\Entity\HorarioVisitas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HorarioVisitasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('area', EntityType::class, [
                'class' => Area::class,
                'choice_label' => 'nombre',
                'label' => 'Área / Piso',
                'attr' => ['class' => 'form-select']
            ])
            ->add('diaSemana', ChoiceType::class, [
                'label' => 'Día de la Semana',
                'choices' => [
                    'Lunes' => 1,
                    'Martes' => 2,
                    'Miércoles' => 3,
                    'Jueves' => 4,
                    'Viernes' => 5,
                    'Sábado' => 6,
                    'Domingo' => 7,
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('horaInicio', TimeType::class, [
                'label' => 'Hora de Inicio',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('horaFin', TimeType::class, [
                'label' => 'Hora de Finalización',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HorarioVisitas::class,
        ]);
    }
}
