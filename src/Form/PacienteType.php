<?php

namespace App\Form;

use App\Entity\Alergias;
use App\Entity\Discapacidades;
use App\Entity\Enfermedades;
use App\Entity\Paciente;
use App\Entity\Tratamientos;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class PacienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('cedula')
            ->add('telefono')
            ->add('correo')
            ->add('direccion')
            ->add('enfermedades', EntityType::class, [
                'class' => Enfermedades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ]
            ])
            ->add('alergias', EntityType::class, [
                'class' => Alergias::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ]
            ])
            ->add('discapacidades', EntityType::class, [
                'class' => Discapacidades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ]
            ])
            ->add('tratamientos', EntityType::class, [
                'class' => Tratamientos::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ]
            ])
            ->add('hasMarcaPaso', CheckboxType::class, [
                'mapped' => false,
                'label' => '¿Tiene marca pasos el paciente?',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paciente::class,
        ]);
    }
}
