<?php

namespace App\Form;

use App\Entity\AltaMedica;
use App\Entity\Emergencia;
use App\Enum\EmergenciasCondicionAlta;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'mapped' => false,
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
                'mapped' => false,
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
            ->add('servicioIngreso', ChoiceType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Servicio de Hospitalización',
                'choices' => [
                    'Medicina Interna' => 'medicina_interna',
                    'Cirugía General' => 'cirugia',
                    'Traumatología' => 'traumatologia',
                    'UCI' => 'uci',
                ],
                'attr' => ['class' => 'form-select noSrchSelect']
            ])
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
