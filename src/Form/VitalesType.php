<?php

namespace App\Form;

use App\Entity\Consulta;
use App\Entity\StatusRecord;
use App\Entity\Vitales;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VitalesType extends AbstractType
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
                    'class' => 'form-control'
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
                    'class' => 'form-control'
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
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la presion diastolica.'),
                ]
            ])
            ->add('frecuenciaCardiaca', NumberType::class, [
                'label' => 'Frecuencia Cardiaca',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
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
                    'class' => 'form-control'
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
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la saturacion de oxigeno.'),
                ]
            ])
            ->add('peso', NumberType::class, [
                'label' => 'Peso',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'data-imc-target' => 'peso',
                    'data-action' => 'input->imc#calculate', // Trigger on every keystroke
                    'step' => '0.1'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el peso.'),
                ]
            ])
            ->add('altura', NumberType::class, [
                'label' => 'Altura',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'data-imc-target' => 'altura',
                    'data-action' => 'input->imc#calculate',
                    'step' => '1'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la altura.'),
                ]
            ])
            ->add('imc', NumberType::class, [
                'label' => 'Indice de Masa Corporal',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control bg-light border-primary fw-bold',
                    'readonly' => true,
                    'data-imc-target' => 'result',
                ],
                'mapped' => true,
            ])
            ->add('cmb', NumberType::class, [
                'label' => 'Circunferencia Media del Brazo (Opcional)',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vitales::class,
        ]);
    }
}
