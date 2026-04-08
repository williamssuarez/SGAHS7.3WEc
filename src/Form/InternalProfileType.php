<?php

namespace App\Form;

use App\Entity\Especialidades;
use App\Entity\InternalProfile;
use App\Entity\Reacciones;
use App\Entity\User;
use App\Form\Type\PhoneType;
use App\Repository\EspecialidadesRepository;
use App\Repository\ReaccionesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class InternalProfileType extends AbstractType
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
                    new NotBlank(message: 'Debe ingresar los nombres.'),
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
                    new NotBlank(message: 'Debe ingresar los apellidos.'),
                ]
            ])
            ->add('telefono', PhoneType::class, [ // This will use the entity's 'telefono' property
                'label' => 'Teléfono',
                //'mapped' => false,
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
            ->add('especialidades', EntityType::class, [
                'class' => Especialidades::class,
                'label' => 'Especialidades',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (EspecialidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InternalProfile::class,
        ]);
    }
}
