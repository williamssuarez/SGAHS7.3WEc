<?php

namespace App\Form;

use App\Entity\CitasSolicitudes;
use App\Entity\Especialidades;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Repository\EspecialidadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitasSolicitudesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('especialidad', EntityType::class, [
                'class' => Especialidades::class,
                'label' => 'Especialidad a Solicitar',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (EspecialidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('motivoConsulta', TextareaType::class, [
                'label' => 'Motivo por el que solicita',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Dolores de espalda frecuenes...',
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitasSolicitudes::class,
        ]);
    }
}
