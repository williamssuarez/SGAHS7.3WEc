<?php

// src/Form/IndicacionMedicaType.php
namespace App\Form;

use App\Entity\IndicacionMedica;
use App\Enum\HospitalizacionCondicionGeneral;
use App\Enum\IndicacionMedicaTipo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndicacionMedicaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipo', EnumType::class, [
                'class' => IndicacionMedicaTipo::class,
                'label' => 'Tipo de Orden',
                'attr' => [
                    'class' => 'form-select noSrchSelect mb-3',
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (IndicacionMedicaTipo $choice) => $choice->getReadableText(),
            ])
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción (Medicamento/Orden)',
                'attr' => ['placeholder' => 'Ej: Ceftriaxona 1g', 'class' => 'form-control']
            ])
            ->add('viaAdministracion', ChoiceType::class, [
                'label' => 'Vía',
                'choices' => [
                    'N/A (No aplica)' => 'N/A',
                    'Vía Oral (VO)' => 'VO',
                    'Intravenosa (IV)' => 'IV',
                    'Intramuscular (IM)' => 'IM',
                    'Subcutánea (SC)' => 'SC',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('frecuencia', TextType::class, [
                'label' => 'Frecuencia / Horario',
                'attr' => ['placeholder' => 'Ej: Cada 8 horas', 'class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IndicacionMedica::class,
        ]);
    }
}
