<?php

// src/Form/AltaHospitalariaType.php
namespace App\Form;

use App\Entity\Hospitalizacion;
use App\Entity\Hospitalizaciones;
use App\Enum\HospitalizacionCondicionAlta;
use App\Enum\HospitalizacionCondicionGeneral;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'attr' => ['class' => 'form-select fw-bold'],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (HospitalizacionCondicionAlta $choice) => $choice->getReadableText(),
            ])
            ->add('diagnosticoEgreso', TextareaType::class, [
                'label' => 'Diagnóstico Final (Epicrisis)',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Resumen clínico del ingreso, evolución y diagnóstico final...',
                    'class' => 'form-control'
                ]
            ])
            // Optional: If you added a field for home instructions
            ->add('indicacionesAlta', TextareaType::class, [
                'label' => 'Indicaciones y Tratamiento para el Hogar',
                'mapped' => false, // Set to true if you add this field to your entity!
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
