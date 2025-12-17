<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class,
                [
                    'label' => 'Archivo',
                    'label_attr' => [
                        'class' => 'input-group-text'
                    ],
                    'attr' => [
                        'class' => 'form-control'
                    ],
                    'mapped' => false,
                    'required' => true,
                    'constraints' => [
                        new File(
                            maxSize: '2M',
                            maxSizeMessage: 'El archivo es demasiado grande. El tamaño maximo permitido es 2MB.',
                            extensions: ['jpg', 'png', 'pdf'],
                            extensionsMessage: 'Por Favor suba un archivo valido. Los tipos de archivos validos son .jpg .png .pdf',
                        )
                    ],
            ])
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del Archivo',
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
            // Configure your form options here
        ]);
    }
}
