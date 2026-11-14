<?php

namespace App\Form;

use App\Entity\Especialidades;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EspecialidadesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de la Especialidad',
                'empty_data' => '',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre'),
                    new Length(max: 255, maxMessage: 'El nombre no puede superar los {{ limit }} caracteres'),
                ]
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción de la Especialidad',
                'empty_data' => '',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 255,
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar una descripción'),
                    new Length(max: 255, maxMessage: 'La descripción no puede superar los {{ limit }} caracteres'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Especialidades::class,
        ]);
    }
}
