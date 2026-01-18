<?php

namespace App\Form;

use App\Entity\ExternalProfile;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use App\Entity\User;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el correo electronico.'),
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => false,
                'label' => 'Roles',
                'attr' => [
                    'class' => 'srchSelect',
                    'data-placeholder' => 'Seleccione los roles...',
                ],
                'choices' => [
                    'Administrador del Sistema' => User::ROLE_ADMIN,
                    'Staff Medico' => User::ROLE_INTERNAL
                ],
                'invalid_message' => 'El rol seleccionado no es valido',
            ])
            ->add('avatarUrl', FileType::class, [
                'label' => 'Imagen de perfil',
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
            ->add('internalProfile', InternalProfileType::class, [
                'label' => false,
                'required' => true,
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
