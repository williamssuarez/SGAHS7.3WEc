<?php

namespace App\Form;

use App\Entity\HospitalizacionCama;
use App\Entity\Habitacion;
use App\Entity\ZonaCama;
use App\Repository\HabitacionRepository;
use App\Repository\ZonaCamaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CamaHospitalizacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codigo', TextType::class, [
                'label' => 'Código de la cama',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: CAMA-01'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre o codigo identificador para la cama'),
                ]
            ])
            ->add('habitacion', EntityType::class, [
                'class' => Habitacion::class,
                'label' => 'Habitacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (HabitacionRepository $er) {
                    return $er->getActivesforSelect();
                },
                'group_by' => function(Habitacion $habitacion) {
                    return $habitacion->getArea()->getNombre(); // Creates the <optgroup label="Área Name">
                },
                'placeholder' => 'Seleccione una habitación...',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HospitalizacionCama::class,
        ]);
    }
}
