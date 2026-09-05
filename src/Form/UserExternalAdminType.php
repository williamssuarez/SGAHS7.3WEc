<?php

namespace App\Form;

use App\Entity\ExternalProfile;
use App\Entity\User;
use App\Enum\SangreTipos;
use App\Form\Type\LocationSelectorType;
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

class UserExternalAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombres',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Primer y Segundo nombre.'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar sus nombres.'),
                ]
            ])
            ->add('apellido', TextType::class, [
                'label' => 'Apellidos',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Primer y Segundo apellido.'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar sus apellidos.'),
                ]
            ])
            ->add('telefono', PhoneType::class, [ // This will use the entity's 'telefono' property
                'label' => 'Teléfono',
                //'mapped' => false,
            ])
            ->add('location', LocationSelectorType::class, [
                'label' => false,
            ])
            ->add('direccion', TextareaType::class, [
                'label' => 'Direccion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar su direccion.'),
                ]
            ])
            ->add('nroDocumento', NumberType::class, [
                'label' => 'Documento de Identidad',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxlength' => '8'
                ],
                'required' => true,
            ])
            ->add('tipoDocumento', ChoiceType::class, [
                'choices'  => [
                    'V' => 'V',
                    'E' => 'E'
                ],
                'attr' => ['class' => 'noSrchSelect']
            ])
            ->add('sangreTipo', EnumType::class, [
                'class' => SangreTipos::class,
                'label' => 'Tipo de Sangre',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (SangreTipos $choice) => $choice->getReadableText(),
            ])
            ->add('sexo', ChoiceType::class, [
                'label' => 'Sexo',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choices'  => [
                    'Masculino' => 'M',
                    'Femenino' => 'F'
                ],
                'attr' => ['class' => 'noSrchSelect']
            ])
            ->add(
                'fechaNacimiento',
                DateType::class,
                [
                    //'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'label' => 'Fecha de Nacimiento',
                    'label_attr' => [
                        'class' => 'form-label mask'
                    ],
                    'attr' => [
                        'class' => 'mask form-control',
                        'data-inputmask' => " 'alias': 'date', 'clearIncomplete': true "
                    ],
                    'required' => true,
                    //'data' => new \DateTime(),
                ]
            )

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
            'data_class' => ExternalProfile::class,
        ]);
    }
}
