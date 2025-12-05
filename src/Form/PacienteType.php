<?php

namespace App\Form;

use App\Entity\Alergias;
use App\Entity\Discapacidades;
use App\Entity\Enfermedades;
use App\Entity\Paciente;
use App\Entity\Tratamientos;
use App\Repository\AlergiasRepository;
use App\Repository\DiscapacidadesRepository;
use App\Repository\EnfermedadesRepository;
use App\Repository\TratamientosRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class PacienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombres del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debe ingresar un nombre',
                    ]),
                ]
            ])
            ->add('apellido', TextType::class, [
                'label' => 'Apellidos del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Debe ingresar un apellido',
                    ]),
                ]
            ])
            ->add('cedula', NumberType::class, [
                'label' => 'Cedula del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only'
                ],
                'required' => true,
            ])
            ->add('telefono', TextType::class, [
                'label' => 'Telefono del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only'
                ],
                'required' => true,
            ])
            ->add('correo', EmailType::class, [
                'label' => 'Correo del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('direccion', TextareaType::class, [
                'label' => 'Direccion del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('enfermedades', EntityType::class, [
                'class' => Enfermedades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'query_builder' => function (EnfermedadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('alergias', EntityType::class, [
                'class' => Alergias::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'query_builder' => function (AlergiasRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('discapacidades', EntityType::class, [
                'class' => Discapacidades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'query_builder' => function (DiscapacidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('tratamientos', EntityType::class, [
                'class' => Tratamientos::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'query_builder' => function (TratamientosRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('hasMarcaPaso', CheckboxType::class, [
                'mapped' => false,
                'label' => '¿Tiene marca pasos el paciente?',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input bigCheckbox'
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
