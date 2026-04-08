<?php

// src/Form/EvolucionHospitalariaType.php
namespace App\Form;

use App\Entity\EvolucionHospitalaria;
use App\Enum\EmergenciasCondicionAlta;
use App\Enum\HospitalizacionCondicionGeneral;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvolucionHospitalariaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*->add('condicionGeneral', ChoiceType::class, [
                'label' => 'Condición General',
                'choices' => [
                    'Estable' => 'Estable',
                    'Delicado' => 'Delicado',
                    'Grave' => 'Grave',
                    'Pronóstico Reservado' => 'Pronóstico Reservado',
                ],
                'attr' => ['class' => 'form-select mb-3']
            ])*/
            ->add('condicionGeneral', EnumType::class, [
                'class' => HospitalizacionCondicionGeneral::class,
                'label' => 'Condición General',
                'attr' => [
                    'class' => 'form-select noSrchSelect mb-3',
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (HospitalizacionCondicionGeneral $choice) => $choice->getReadableText(),
            ])
            ->add('subjetivo', TextareaType::class, [
                'label' => 'S - Subjetivo (Lo que refiere el paciente)',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ej: El paciente refiere haber pasado buena noche, disminución del dolor...',
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('objetivo', TextareaType::class, [
                'label' => 'O - Objetivo (Examen físico y signos)',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ej: Paciente consciente, orientado. Abdomen blando, depresible...',
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('analisis', TextareaType::class, [
                'label' => 'A - Análisis (Impresión diagnóstica)',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ej: Neumonía adquirida en la comunidad, evolución clínica favorable...',
                    'class' => 'form-control mb-3'
                ]
            ])
            ->add('plan', TextareaType::class, [
                'label' => 'P - Plan (Conducta y tratamiento)',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ej: 1. Continuar antibioticoterapia. 2. Iniciar dieta tolerada. 3. Laboratorios de control...',
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvolucionHospitalaria::class,
        ]);
    }
}
