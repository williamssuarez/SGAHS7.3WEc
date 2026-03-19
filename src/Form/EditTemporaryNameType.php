<?php

namespace App\Form;

use App\Entity\Emergencia;
use App\Entity\Paciente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditTemporaryNameType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pacienteTemporal', TextType::class, [
            'label' => 'Nombre Temporal (En caso de no poder identificar)',
            'label_attr' => [
                'class' => 'form-label'
            ],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => 'Ej: Hombre alto de lentes...',
            ],
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Emergencia::class]);
    }
}
