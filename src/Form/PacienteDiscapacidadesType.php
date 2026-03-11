<?php

namespace App\Form;

use App\Entity\Discapacidades;
use App\Entity\Paciente;
use App\Entity\PacienteDiscapacidades;
use App\Entity\StatusRecord;
use App\Repository\DiscapacidadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PacienteDiscapacidadesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discapacidad', EntityType::class, [
                'class' => Discapacidades::class,
                'label' => 'Discapacidades',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (DiscapacidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('porcentaje', RangeType::class, [
                'label' => 'Grado de Discapacidad',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                    'class' => 'form-range', // Bootstrap class
                    'data-percentage-badge-target' => 'input',
                    'data-action' => 'input->percentage-badge#update'
                ],
            ])
            ->add('congenita', CheckboxType::class, [
                'label' => '¿Congenita?',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'required' => false,
            ])
            ->add('ayudaTecnica', TextType::class, [
                'label' => 'Ayuda Tecnica',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'El: El paciente necesita de silla de ruedas para movilizarse...'
                ],
                'required' => false,
            ])
            ->add('numeroCertificado', TextType::class, [
                'label' => 'Numero de Certificado',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('limitacionesFuncionales', TextareaType::class, [
                'label' => 'Notas/Comentarios Adicionales',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: El paciente no puede usar escaleras sin ayuda...',
                ],
                'required' => false
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Notas/Comentarios Adicionales',
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
            'data_class' => PacienteDiscapacidades::class,
        ]);
    }
}
