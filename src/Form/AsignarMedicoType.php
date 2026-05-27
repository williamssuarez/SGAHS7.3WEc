<?php

// src/Form/AsignarMedicoType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsignarMedicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('medico', ChoiceType::class, [
            'choices' => $options['medicos'],
            // Map the object to a readable string for the dropdown
            'choice_label' => function (?User $user) {
                return $user ? 'Dr(a). ' . $user->getInternalProfile()->getNombre() . ' ' . $user->getInternalProfile()->getApellido() : '';
            },
            // Map the object to its ID for the HTML value attribute
            'choice_value' => 'id',
            'placeholder' => '--- Seleccione un Médico ---',
            'label' => 'Médico Tratante',
            'attr' => [
                'class' => 'form-select select2-modal',
                'style' => 'width: 100%'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Require the medicos array to be passed from the controller
        $resolver->setRequired('medicos');
    }
}
