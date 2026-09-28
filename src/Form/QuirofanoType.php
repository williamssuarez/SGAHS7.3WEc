<?php

namespace App\Form;

use App\Entity\Quirofano;
use App\Entity\StatusRecord;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuirofanoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del Quirófano',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Quirófano 1'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre'),
                ]
            ])
            ->add('estado', ChoiceType::class, [
                'label' => 'Estado Operativo',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'choices' => [
                    'Disponible' => 'available',
                    'En Mantenimiento' => 'maintenance',
                ],
                'attr' => [
                    'class' => 'form-select noSrchSelect'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quirofano::class,
        ]);
    }
}
