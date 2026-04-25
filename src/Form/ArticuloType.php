<?php

namespace App\Form;

use App\Entity\Articulo;
use App\Enum\ArticuloCategoria;
use App\Enum\DiscapacidadesTipos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticuloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Propofol 10mg/ml...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el nombre del articulo.'),
                ]
            ])
            ->add('codigoBarras', TextType::class, [
                'label' => 'Codigo de Barras',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: HS-1000433232549678...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el codigo de barras.'),
                ]
            ])
            ->add('categoria', EnumType::class, [
                'class' => ArticuloCategoria::class,
                'label' => 'Categoria',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (ArticuloCategoria $choice) => $choice->getReadableText(),
            ])
            ->add('unidadMedida', TextType::class, [
                'label' => 'Unidad',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: 1 tableta, 5ml, 2 gotas...'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el tipo de unidad.'),
                ]
            ])
            ->add('stockMinimo', NumberType::class, [
                'label' => 'Stock Minimo',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'placeholder' => 'Ej: 1, 2, 33'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el stock minimo.'),
                ]
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Anestesiologo de rapido efecto...',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe detallar la descripcion del articulo.'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articulo::class,
        ]);
    }
}
