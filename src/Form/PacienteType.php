<?php

namespace App\Form;

use App\Entity\Alergias;
use App\Entity\Discapacidades;
use App\Entity\Enfermedades;
use App\Entity\Paciente;
use App\Entity\Tratamientos;
use App\Form\DataTransformer\PhoneNumberTransformer;
use App\Form\Type\PhoneType;
use App\Repository\AlergiasRepository;
use App\Repository\DiscapacidadesRepository;
use App\Repository\EnfermedadesRepository;
use App\Repository\TratamientosRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class PacienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombres del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un nombre'),
                ]
            ])
            ->add('apellido', TextType::class, [
                'label' => 'Apellidos del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar un apellido'),
                ]
            ])
            ->add('cedula', NumberType::class, [
                'label' => 'Cedula del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxlength' => '9'
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
                    'required' => false,
                    //'data' => new \DateTime(),
                ]
            )
            ->add('foto', FileType::class,
                [
                    'label' => 'Foto del Paciente',
                    'label_attr' => [
                        'class' => 'input-group-text'
                    ],
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File(
                            maxSize: '2M',
                            maxSizeMessage: 'El archivo es demasiado grande. El tamaño maximo permitido es 2MB.',
                            extensions: ['jpg', 'png'],
                            extensionsMessage: 'Por Favor suba un archivo valido. Los tipos de archivos validos son .jpg .png',
                        )
                    ],
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
            ->add('telefono', PhoneType::class, [ // This will use the entity's 'telefono' property
                'label' => 'Teléfono',
                //'mapped' => false,
            ])
            ->add('correo', EmailType::class, [
                'label' => 'Correo del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('direccion', TextareaType::class, [
                'label' => 'Direccion del Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('enfermedades', EntityType::class, [
                'class' => Enfermedades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => false,
                'query_builder' => function (EnfermedadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('alergias', EntityType::class, [
                'class' => Alergias::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => false,
                'query_builder' => function (AlergiasRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('discapacidades', EntityType::class, [
                'class' => Discapacidades::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => false,
                'query_builder' => function (DiscapacidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('tratamientos', EntityType::class, [
                'class' => Tratamientos::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => false,
                'query_builder' => function (TratamientosRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('hasMarcaPaso', CheckboxType::class, [
                'label' => '¿Tiene marca pasos el paciente?',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input bigCheckbox'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paciente::class,
            'empty_data' => fn (FormInterface $form) => $form->get('startTelefono')->getData() . '-' . $form->get('telefonoExtension')->getData(),
        ]);
    }
}
