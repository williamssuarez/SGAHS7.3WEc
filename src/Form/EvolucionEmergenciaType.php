<?php

namespace App\Form;

use App\Entity\EvolucionEmergencia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvolucionEmergenciaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notaClinica', TextareaType::class, [
                'label' => 'Nota de Evolución / Órdenes',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Describa el estado del paciente, examen físico, plan de tratamiento...',
                    'class' => 'form-control'
                ]
            ])
            // --- Optional Vitals ---
            ->add('paSistolica', NumberType::class, [
                'label' => 'PA Sistólica',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm number-only',
                    'maxLength' => 3,
                    'placeholder' => 'Ej: 120'
                ],
                'required' => false,
            ])
            ->add('paDiastolica', NumberType::class, [
                'label' => 'PA Diastólica',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm number-only',
                    'maxLength' => 3,
                    'placeholder' => 'Ej: 80'
                ],
                'required' => false,
            ])
            ->add('frecuenciaCardiaca', NumberType::class, [
                'label' => 'Frec. Cardíaca',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm number-only',
                    'maxLength' => 3,
                    'placeholder' => 'Ej: 75'
                ],
                'required' => false,
            ])
            ->add('frecuenciaRespiratoria', NumberType::class, [
                'label' => 'Frec. Resp.',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm number-only',
                    'maxLength' => 3,
                    'placeholder' => 'Ej: 18'
                ],
                'required' => false,
            ])
            ->add('temperatura', NumberType::class, [
                'label' => 'Temp (°C)',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm',
                    'data-controller' => 'mask',
                    'data-mask-type-value' => 'decimal',
                    'placeholder' => 'Ej: 37.5'
                ],
                'required' => false,
            ])
            ->add('spo2', NumberType::class, [
                'label' => 'SpO2 (%)',
                'label_attr' => [
                    'class' => 'small fw-bold text-muted mb-1'
                ],
                'attr' => [
                    'class' => 'form-control form-control-sm number-only',
                    'placeholder' => 'Ej: 98'
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EvolucionEmergencia::class,
        ]);
    }
}
