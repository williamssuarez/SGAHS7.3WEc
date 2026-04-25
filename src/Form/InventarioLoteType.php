<?php

// src/Form/InventarioLoteType.php
namespace App\Form;

use App\Entity\Articulo;
use App\Entity\InventarioLote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class InventarioLoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('articulo', EntityType::class, [
                'class' => Articulo::class,
                'choice_label' => 'nombre',
                'label' => 'Artículo / Insumo',
                'attr' => ['class' => 'form-select srchSelect']
            ])
            ->add('lote', TextType::class, [
                'label' => 'Código de Lote',
                'attr' => ['placeholder' => 'Ej: L-2026-X1', 'class' => 'form-control text-uppercase']
            ])
            ->add('fechaCaducidad', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Caducidad',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new GreaterThanOrEqual('today', message: 'No puede ingresar insumos ya caducados.')
                ]
            ])
            ->add('cantidadActual', NumberType::class, [
                'label' => 'Cantidad a Ingresar',
                'attr' => [
                    'class' => 'form-control number-only',
                    'min' => 1
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la cantidad de recargas permitidas.'),
                ]
            ])
            ->add('precioCompra', NumberType::class, [
                'label' => 'Costo de Compra (Unitario)',
                'attr' => [
                    'class' => 'form-control',
                    'data-controller' => 'mask',
                    'data-mask-type-value' => 'decimal', // If you want 170.00
                    'data-margin-calculator-target' => 'compra',
                    'data-action' => 'input->margin-calculator#calculate',
                ]
            ])
            ->add('precioVenta', NumberType::class, [
                'label' => 'Precio de Venta (Paciente)',
                'attr' => [
                    'class' => 'form-control',
                    'data-controller' => 'mask',
                    'data-mask-type-value' => 'decimal', // If you want 170.00
                    'step' => '0.01',
                    'data-margin-calculator-target' => 'venta',
                    'data-action' => 'input->margin-calculator#calculate'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InventarioLote::class,
        ]);
    }
}
