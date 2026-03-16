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
            ->add('paSistolica', IntegerType::class, [
                'label' => 'PA Sistólica',
                'required' => false,
            ])
            ->add('paDiastolica', IntegerType::class, [
                'label' => 'PA Diastólica',
                'required' => false,
            ])
            ->add('frecuenciaCardiaca', IntegerType::class, [
                'label' => 'Frec. Cardíaca',
                'required' => false,
            ])
            ->add('frecuenciaRespiratoria', IntegerType::class, [
                'label' => 'Frec. Resp.',
                'required' => false,
            ])
            ->add('temperatura', NumberType::class, [
                'label' => 'Temp (°C)',
                'required' => false,
            ])
            ->add('spo2', NumberType::class, [
                'label' => 'SpO2 (%)',
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
