<?php

namespace App\Form;

use App\Entity\Especialidades;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use App\Enum\EnfermedadesCategorias;
use App\Enum\TriageNivelesPrioridad;
use App\Repository\EspecialidadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmergenciaTriageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('temperatura', NumberType::class, [
                'label' => 'Temperatura',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'data-controller' => 'mask',
                    'data-mask-type-value' => 'decimal', // If you want 170.00
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la temperatura.'),
                ]
            ])
            ->add('paSistolica', NumberType::class, [
                'label' => 'Presion Sistolica',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la presion sistolica.'),
                ]
            ])
            ->add('paDiastolica', NumberType::class, [
                'label' => 'Presion Diastolica',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la presion diastolica.'),
                ]
            ])
            ->add('frecuenciaCardiaca', NumberType::class, [
                'label' => 'Frecuencia Cardiaca',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la frecuencia cardiaca.'),
                ]
            ])
            ->add('frecuenciaRespiratoria', NumberType::class, [
                'label' => 'Frecuencia Respiratoria',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la frecuencia respiratoria.'),
                ]
            ])
            ->add('spo2', NumberType::class, [
                'label' => 'Saturacion de Oxigeno',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la saturacion de oxigeno.'),
                ]
            ])
            ->add('nivelPrioridad', EnumType::class, [
                'class' => TriageNivelesPrioridad::class,
                'label' => 'Nivel de Prioridad (Triage)',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (TriageNivelesPrioridad $choice) => $choice->getReadableText(),
            ])
            ->add('escalaGlasgow', TextType::class, [
                'label' => 'Escala de Glasgow (3-15)',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'minLength' => '1',
                    'maxlength' => '2'
                ],
                'required' => false,
            ])
            ->add('motivoIngreso', TextareaType::class, [
                'label' => 'Motivo de Ingreso / Sintomas',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Dolor torácico opresivo irradiado al brazo izquierdo...'
                ],
                'required' => true
            ])
            ->add('sendConsultation', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Enviar a Consulta Externa (No requiere cama)',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'attr' => [
                    'class' => 'form-check-input',
                    'data-triage-routing-target' => 'checkbox',
                    'data-action' => 'change->triage-routing#toggleSpecialty'
                ]
            ])
            ->add('specialty', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'class' => Especialidades::class,
                'label' => 'Especialidad',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'placeholder' => 'Seleccione especialidad...',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'query_builder' => function (EspecialidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Triage::class,
        ]);
    }
}
