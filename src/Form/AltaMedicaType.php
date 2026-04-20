<?php

namespace App\Form;

use App\Entity\Alergenos;
use App\Entity\AltaMedica;
use App\Entity\Area;
use App\Entity\Emergencia;
use App\Enum\EmergenciasCondicionAlta;
use App\Repository\AlergenosRepository;
use App\Repository\AreaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AltaMedicaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('condicionAlta', EnumType::class, [
                'class' => EmergenciasCondicionAlta::class,
                'label' => 'Condición de Egreso',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-select noSrchSelect',
                    'data-discharge-routing-target' => 'condition',
                    'data-action' => 'change->discharge-routing#toggleFields'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (EmergenciasCondicionAlta $choice) => $choice->getReadableText(),
            ])
            // --- TRANSFER FIELDS ---
            ->add('hospitalDestino', TextType::class, [
                'required' => false,
                'label' => 'Hospital / Clínica de Destino',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Hospital de los Samanes...'
                ]
            ])
            ->add('motivoTraslado', TextareaType::class, [
                'required' => false,
                'label' => 'Motivo del Traslado',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ej: Falta de Especialista, Decisión familiar...'
                ]
            ])
            // --- ADMISSION FIELDS ---
            /*->add('servicioIngreso', ChoiceType::class, [
                'required' => false,
                'label' => 'Servicio de Hospitalización',
                'choices' => [
                    'Medicina Interna' => 'medicina_interna',
                    'Cirugía General' => 'cirugia',
                    'Traumatología' => 'traumatologia',
                    'UCI' => 'uci',
                ],
                'attr' => ['class' => 'form-select noSrchSelect'],
                'placeholder' => 'Seleccione...',
            ])*/
            ->add('areaHospitalizacion', EntityType::class, [
                'class' => Area::class,
                'label' => 'Servicio de Hospitalización',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'form-select srchSelect'
                ],
                'required' => true,
                'query_builder' => function (AreaRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            // --- DECEASED FIELDS ---
            ->add('fechaMuerte', DateTimeType::class, [
                    //'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'label' => 'Fecha y Hora de Muerte',
                    'label_attr' => [
                        'class' => 'form-label mask'
                    ],
                    'with_seconds' => false,
                    'model_timezone' => 'UTC',              // Como se guarda en la db
                    'view_timezone' => 'America/Caracas',   // Como la escribe el doctor
                    'attr' => [
                        'class' => 'mask form-control',
                        'data-inputmask' => " 'alias': 'date', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy HH:MM' "
                    ],
                    'required' => false,
                    //'data' => new \DateTime(),
                ]
            )
            ->add('diagnosticoFinal', TextareaType::class, [
                'label' => 'Diagnóstico Final',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ej: Apendicitis aguda, resuelta quirúrgicamente...'
                ]
            ])
            ->add('indicacionesMedicas', TextareaType::class, [
                'label' => 'Indicaciones Médicas / Tratamiento al Alta',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Ej: Reposo por 3 días, Ibuprofeno 400mg cada 8h...'
                ],
                'required' => false
            ])
            ->add('needsCleaning', CheckboxType::class, [
                'label' => '¿La cama requiere limpieza?',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => [
                    'class' => 'form-check-input bigCheckbox',
                    'data-conditional-field-target' => 'trigger',
                    'data-action' => 'change->conditional-field#toggle'
                ],
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AltaMedica::class,
        ]);
    }
}
