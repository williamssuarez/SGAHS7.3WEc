<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Correo'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un correo.'),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Estoy de acuerdo con los terminos de servicio',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'constraints' => [
                    new IsTrue(message: 'Debe estar de acuerdo con los terminos.'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank(message: 'Debe ingresar una clave.'),
                        new Length(min: 12, max: 4096, minMessage: 'La clave debe ser al menos {{ limit }} caracteres'),
                        new PasswordStrength(minScore: PasswordStrength::STRENGTH_MEDIUM),
                        new NotCompromisedPassword(),
                    ],
                    'label' => 'Nueva Clave',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Nueva Clave',
                    ],
                    'required' => true,
                ],
                'second_options' => [
                    'label' => 'Repita la Clave',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Repita la Clave',
                    ],
                    'required' => true,
                ],
                'invalid_message' => 'Las claves deben coincidir.',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
