<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Validator\Constraints\Length;

class PatientLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipoDocumento', ChoiceType::class, [
                'label' => 'Tipo de Documento',
                'choices'  => [
                    'V' => 'V',
                    'E' => 'E'
                ],
                'attr' => ['class' => 'noSrchSelect'],
                'required' => true,
            ])
            ->add('cedula', NumberType::class, [
                'label' => 'Número de Documento (Cédula)',
                'attr' => [
                    'class' => 'form-control number-only',
                    'placeholder' => 'Ej. 12345678',
                    'maxlength' => '8'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Debe ingresar una cédula']),
                ]
            ])
            ->add('codigo', TextType::class, [
                'label' => 'Código de Vinculación Web',
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'Ej. A1B2C3',
                    'style' => 'letter-spacing: 2px;',
                    'maxlength' => '6'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Debe ingresar el código']),
                    new Length([
                        'min' => 6,
                        'max' => 6,
                        'exactMessage' => 'El código debe tener exactamente {{ limit }} caracteres.',
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
