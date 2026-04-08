<?php

namespace App\Form;

use App\Entity\Alergenos;
use App\Entity\Area;
use App\Entity\Habitacion;
use App\Repository\AlergenosRepository;
use App\Repository\AreaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class HabitacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de la habitacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: A-11'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre'),
                ]
            ])
            ->add('ubicacion', TextType::class, [
                'label' => 'Ubicacion de la habitacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Piso 1, al final del pasillo...'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la ubicacion'),
                ]
            ])
            ->add('area', EntityType::class, [
                'class' => Area::class,
                'label' => 'Area',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (AreaRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Habitacion::class,
        ]);
    }
}
