<?php

namespace App\Form;

use App\Entity\HistoriaPaciente;
use App\Entity\Medicamentos;
use App\Entity\Paciente;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriaPacienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latidos')
            ->add('tipoProcedimiento', ChoiceType::class, [
                'choices'  => [
                    'Consulta' => 'Consulta',
                    'Emergencia' => 'Emergencia',
                    'Examen' => 'Examen',
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ]
            ])
            ->add('descripcionProdecimiento')
            ->add('medicamentos', EntityType::class, [
                'class' => Medicamentos::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ]
            ])
            ->add('medicamentosObservaciones')
            ->add('observaciones')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistoriaPaciente::class,
        ]);
    }
}
