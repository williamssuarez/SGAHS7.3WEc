<?php

namespace App\Form;

use App\Entity\Discapacidades;
use App\Entity\Paciente;
use App\Entity\PacienteDiscapacidades;
use App\Entity\StatusRecord;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PacienteDiscapacidadesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('porcentaje')
            ->add('congenita')
            ->add('ayudaTecnica')
            ->add('limitacionesFuncionales')
            ->add('numeroCertificado')
            ->add('observaciones')
            ->add('uidCreate')
            ->add('uidUpdate')
            ->add('created', null, [
                'widget' => 'single_text',
            ])
            ->add('updated', null, [
                'widget' => 'single_text',
            ])
            ->add('paciente', EntityType::class, [
                'class' => Paciente::class,
                'choice_label' => 'id',
            ])
            ->add('discapacidad', EntityType::class, [
                'class' => Discapacidades::class,
                'choice_label' => 'id',
            ])
            ->add('status', EntityType::class, [
                'class' => StatusRecord::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PacienteDiscapacidades::class,
        ]);
    }
}
