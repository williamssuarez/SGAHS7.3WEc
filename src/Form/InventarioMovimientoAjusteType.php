<?php

// src/Form/MovimientoAjusteType.php
namespace App\Form;

use App\Entity\MovimientoInventario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class InventarioMovimientoAjusteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad', IntegerType::class, [
                'label' => 'Cantidad a Ajustar (+ o -)',
                'attr' => [
                    'class' => 'form-control form-control-lg text-center fw-bold',
                    'placeholder' => 'Ej: -2 o 5',
                    'data-stock-adjuster-target' => 'input',
                    'data-action' => 'input->stock-adjuster#calculate'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar una cantidad.'),
                    new NotEqualTo(value: 0, message: 'El ajuste no puede ser cero.')
                ]
            ])
            ->add('referenciaOrigen', TextareaType::class, [
                'label' => 'Motivo del Ajuste (Obligatorio)',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ej: Frascos rotos durante el traslado, inventario físico no coincide, etc.'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe justificar el motivo de este ajuste.')
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MovimientoInventario::class,
        ]);
    }
}
