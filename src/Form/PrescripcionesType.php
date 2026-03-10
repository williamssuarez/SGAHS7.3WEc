<?php

namespace App\Form;

use App\Entity\Medicamentos;
use App\Entity\Paciente;
use App\Entity\Prescripciones;
use App\Entity\StatusRecord;
use App\Enum\PrescripcionesEstados;
use App\Repository\MedicamentosRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PrescripcionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicamento', EntityType::class, [
                'class' => Medicamentos::class,
                'label' => 'Medicamento',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                //'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (MedicamentosRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('dosis', TextType::class, [
                'label' => 'Dosis a tomar',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: 1 tableta, 5ml, 2 gotas...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la dosis de la prescripcion.'),
                ]
            ])
            ->add('frecuencia', TextType::class, [
                'label' => 'Frecuencia',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: 1 vez al dia, 3 veces al dia, segun sea necesario...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe escribir la frecuencia de la prescripcion.'),
                ]
            ])
            ->add('detallesFrecuencia', TextareaType::class, [
                'label' => 'Detalles de la frecuencia',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Tomar después del almuerzo, solo si hay fiebre, antes de desayunar...',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe detallar la frecuencia de la prescripcion.'),
                ]
            ])
            ->add('estado', EnumType::class, [
                'class' => PrescripcionesEstados::class,
                'label' => 'Estado de la Prescripcion',
                'expanded' => true,
                'multiple' => false,
                'choice_label' => fn (PrescripcionesEstados $choice) => $choice->getReadableText(),
                'choice_attr' => function(PrescripcionesEstados $choice) {
                    return [
                        'class' => 'form-check-input',
                        'data-medication-status-target' => 'state', // Correct controller name
                        'data-action' => 'change->medication-status#toggle'
                    ];
                },
            ])
            ->add('fechaInicio', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Inicio',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'model_timezone' => 'UTC',              // Como se guarda en la db
                'view_timezone' => 'America/Caracas',   // Como la escribe el doctor
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy' "
                ],
                'required' => true,
                //'data' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
            ])
            ->add('fechaFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Finalizacion',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Caracas',
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy' "
                ],
                'row_attr' => [
                    'data-toggle-date-target' => 'endDateContainer',
                    'class' => 'd-none'
                ],
                'required' => false,
            ])
            ->add('cantidadDispensar', NumberType::class, [
                'label' => 'Cantidad a Dispensar',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'placeholder' => 'Ej: 1, 2, 33'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la cantidad a dispensar.'),
                ]
            ])
            ->add('tipoDispensa', TextType::class, [
                'label' => 'Tipo de Dispensa',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Cajas, Ampollas, Parche, Tabletas...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el tipo de dispensa.'),
                ]
            ])
            ->add('recarga', NumberType::class, [
                'label' => 'Cantidad de recargas',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'placeholder' => 'Ej: 0, 1, 3'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la cantidad de recargas permitidas.'),
                ]
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Razon de la suspension',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Suspendido por reaccion alergica...',
                ],
                'required' => false,
                /*'row_attr' => [
                    'data-medication-status-target' => 'reasonContainer',
                    'class' => 'd-none mb-3' // Hidden by default
                ],*/
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescripciones::class,
        ]);
    }
}
