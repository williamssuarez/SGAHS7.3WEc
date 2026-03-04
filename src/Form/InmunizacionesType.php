<?php

namespace App\Form;

use App\Entity\Inmunizaciones;
use App\Enum\InmunizacionesAdministraciones;
use App\Enum\InmunizacionesTipos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class InmunizacionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de Inmunizacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre'),
                ]
            ])
            ->add('tipo', EnumType::class, [
                'class' => InmunizacionesTipos::class,
                'label' => 'Tipo de Inmunizacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (InmunizacionesTipos $choice) => $choice->getReadableText(),
            ])
            ->add('administracion', EnumType::class, [
                'class' => InmunizacionesAdministraciones::class,
                'label' => 'Via de Administracion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (InmunizacionesAdministraciones $choice) => $choice->getReadableText(),
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripcion de la Inmunizacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
            ->add('frecuenciaSugerida', TextType::class, [
                'label' => 'Frecuencia Sugerida',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Administrar cada 2 meses'
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inmunizaciones::class,
        ]);
    }
}
