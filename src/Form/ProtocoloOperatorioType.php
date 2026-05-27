<?php

// src/Form/ProtocoloOperatorioType.php
namespace App\Form;

use App\Entity\ProtocoloOperatorio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProtocoloOperatorioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hallazgos', TextareaType::class, [
                'label' => 'Hallazgos Quirúrgicos',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Describa lo que encontró al iniciar el procedimiento...',
                    'class' => 'form-control',
                ]
            ])
            ->add('tecnicaQuirurgica', TextareaType::class, [
                'label' => 'Técnica Quirúrgica (Paso a Paso)',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => 'Describa la incisión, disección, procedimiento principal y cierre...',
                    'class' => 'form-control',
                ]
            ])
            ->add('complicaciones', TextareaType::class, [
                'label' => 'Complicaciones o Incidentes',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Deje en blanco si no hubo complicaciones.',
                    'class' => 'form-control',
                    'rows' => 2,
                ]
            ])
            ->add('sangradoEstimado', IntegerType::class, [
                'label' => 'Sangrado Estimado (cc / ml)',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control',
                ]
            ])
            ->add('envioPatologia', CheckboxType::class, [
                'label' => '¿Se enviaron muestras a Anatomía Patológica (Biopsia)?',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProtocoloOperatorio::class,
        ]);
    }
}
