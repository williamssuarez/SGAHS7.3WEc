<?php
// src/Form/ConsumoQuirurgicoType.php
namespace App\Form;

use App\Entity\Articulo;
use App\Entity\ConsumoQuirurgico;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumoQuirurgicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('aportadoPorPaciente', CheckboxType::class, [
                'label' => 'Insumo aportado por el paciente',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input form-check-input-lg cursor-pointer',
                    'data-hybrid-supply-target' => 'toggle',
                    'data-action' => 'change->hybrid-supply#toggleFields'
                ],
                'label_attr' => ['class' => 'fw-bold text-dark cursor-pointer']
            ])
            ->add('articuloInventario', EntityType::class, [
                'class' => Articulo::class,
                'choice_label' => 'nombre',
                'placeholder' => '--- Buscar en Inventario Hospitalario ---',
                'required' => false,
                'label' => 'Artículo del Hospital',
                'attr' => [
                    'class' => 'form-select srchSelect',
                    'data-hybrid-supply-target' => 'hospitalInput'
                ]
            ])
            ->add('descripcionArticuloExterno', TextType::class, [
                'required' => false,
                'label' => 'Descripción del Insumo Externo',
                'attr' => [
                    'placeholder' => 'Ej: Kit quirúrgico marca X, Malla de titanio...',
                    'class' => 'form-control',
                    'data-hybrid-supply-target' => 'patientInput'
                ]
            ])
            ->add('cantidad', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => ['class' => 'form-control text-center fw-bold', 'min' => 1]
            ])
            ->add('observaciones', TextareaType::class, [
                'required' => false,
                'label' => 'Observaciones (Opcional)',
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Ej: Empaque dañado, lote específico...']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConsumoQuirurgico::class,
        ]);
    }
}
