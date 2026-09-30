<?php

// src/Form/AltaHospitalariaType.php
namespace App\Form;

use App\Entity\Hospitalizaciones;
use App\Enum\HospitalizacionCondicionAlta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AltaHospitalariaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('condicionAlta', EnumType::class, [
                'class' => HospitalizacionCondicionAlta::class,
                'label' => 'Condición de Egreso',
                'attr' => [
                    'class' => 'form-select noSrchSelect',
                    'data-discharge-routing-target' => 'condition'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (HospitalizacionCondicionAlta $choice) => $choice->getReadableText(),
                //'placeholder' => 'Seleccione...',
            ])
            ->add('diagnosticoEgreso', TextareaType::class, [
                'label' => 'Diagnóstico Final (Epicrisis)',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Resumen clínico del ingreso, evolución y diagnóstico final...',
                    'class' => 'form-control'
                ]
            ])
            ->add('hospitalDestino', TextType::class, [
                'label' => 'Hospital/Clínica de Destino',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Hospital Central'
                ]
            ])
            ->add('motivoTraslado', TextareaType::class, [
                'label' => 'Motivo del Traslado',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Requiere Unidad de Cuidados Intensivos'
                ]
            ])
            ->add('fechaMuerte', DateTimeType::class, [
                'label' => 'Fecha y Hora de Fallecimiento',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('indicacionesAlta', TextareaType::class, [
                'label' => 'Indicaciones y Tratamiento para el Hogar',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ej: Reposo absoluto por 3 días. Paracetamol 500mg cada 8h...',
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hospitalizaciones::class,
        ]);
    }
}
