<?php

namespace App\Form;

use App\Entity\Especialidades;
use App\Entity\SignosVitalesHospitalarios;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use App\Enum\EnfermedadesCategorias;
use App\Enum\TriageNivelesPrioridad;
use App\Repository\EspecialidadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignosVitalesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('temperatura', NumberType::class, [
                'label' => 'Temperatura',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'data-controller' => 'mask',
                    'data-mask-type-value' => 'decimal', // If you want 170.00
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la temperatura.'),
                ]
            ])
            ->add('paSistolica', NumberType::class, [
                'label' => 'Presion Sistolica',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la presion sistolica.'),
                ]
            ])
            ->add('paDiastolica', NumberType::class, [
                'label' => 'Presion Diastolica',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la presion diastolica.'),
                ]
            ])
            ->add('frecuenciaCardiaca', NumberType::class, [
                'label' => 'Frecuencia Cardiaca',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la frecuencia cardiaca.'),
                ]
            ])
            ->add('frecuenciaRespiratoria', NumberType::class, [
                'label' => 'Frecuencia Respiratoria',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la frecuencia respiratoria.'),
                ]
            ])
            ->add('spo2', NumberType::class, [
                'label' => 'Saturacion de Oxigeno',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 3,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la saturacion de oxigeno.'),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignosVitalesHospitalarios::class,
        ]);
    }
}
