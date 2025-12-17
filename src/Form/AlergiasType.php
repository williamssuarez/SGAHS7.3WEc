<?php

namespace App\Form;

use App\Entity\Alergenos;
use App\Entity\Alergias;
use App\Entity\Paciente;
use App\Entity\Reacciones;
use App\Repository\ReaccionesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlergiasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('alergeno', EntityType::class, [
                'class' => Alergenos::class,
                'label' => 'Alergeno',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
            ])
            ->add('reacciones', EntityType::class, [
                'class' => Reacciones::class,
                'label' => 'Reacciones',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (ReaccionesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('severidad', ChoiceType::class, [
                'label' => 'Severidad de la Alergia',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choices'  => [
                    'Minima' => 'min',
                    'Moderada' => 'mod',
                    'Severa' => 'sev',
                ],
                'choice_attr' => [
                    'class' => 'form-check-input' // This applies the class to each radio circle
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Observaciones/Comentarios Adicionales',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alergias::class,
        ]);
    }
}
