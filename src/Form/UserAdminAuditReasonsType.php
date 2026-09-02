<?php

namespace App\Form;

use App\Entity\ExternalProfile;
use App\Entity\User;
use App\Enum\SangreTipos;
use App\Form\Type\PhoneType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdminAuditReasonsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            //AUDITORIA
            ->add('password', PasswordType::class, [
                'label' => 'Clave de Administrador',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Introduzca su clave.',
                    'autocomplete' => 'off'
                ],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la clave.'),
                ]
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motivo de la modificacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la razon del cambio.'),
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
