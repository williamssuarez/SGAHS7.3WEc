<?php

namespace App\Form;

use App\Entity\VisitaHospitalaria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisitaHospitalariaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombreVisitante', TextType::class, [
                'label' => 'Nombre Completo del Visitante',
                'attr' => ['placeholder' => 'Nombres y Apellidos', 'class' => 'form-control']
            ])
            ->add('parentesco', ChoiceType::class, [
                'label' => 'Parentesco / Relación',
                'choices' => [
                    'Madre / Padre' => 'Madre/Padre',
                    'Esposo(a) / Pareja' => 'Pareja',
                    'Hijo(a)' => 'Hijo/a',
                    'Hermano(a)' => 'Hermano/a',
                    'Familiar Directo' => 'Familiar',
                    'Amigo(a)' => 'Amigo/a',
                    'Representante Legal' => 'Representante',
                ],
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisitaHospitalaria::class,
        ]);
    }
}
