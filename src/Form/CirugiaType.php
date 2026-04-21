<?php

// src/Form/CirugiaType.php
namespace App\Form;

use App\Entity\Cirugia;
use App\Entity\InternalProfile;
use App\Entity\Paciente;
use App\Entity\Quirofano;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CirugiaType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paciente', EntityType::class, [
                'class' => Paciente::class,
                'label' => 'Paciente',
                'placeholder' => 'Buscar por Cédula o Nombre...',
                'choices' => [], // Empty by default for AJAX
                'attr' => ['class' => 'form-select ajaxSrchSelect'],
                'required' => true,
            ])
            ->add('cirujanoPrincipal', EntityType::class, [
                'class' => InternalProfile::class,
                'choice_label' => function(InternalProfile $profile) {
                    return 'Dr(a). ' . $profile->getNombre();
                },
                'label' => 'Cirujano Principal',
                'attr' => ['class' => 'form-select srchSelect']
            ])
            ->add('procedimientoPropuesto', TextType::class, [
                'label' => 'Procedimiento a Realizar',
                'attr' => ['placeholder' => 'Ej: Apendicectomía Laparoscópica', 'class' => 'form-control']
            ])
            ->add('diagnosticoPreoperatorio', TextType::class, [
                'label' => 'Diagnóstico / Motivo',
                'attr' => ['placeholder' => 'Ej: Apendicitis Aguda', 'class' => 'form-control']
            ])
            ->add('fechaHoraProgramada', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha y Hora Programada',
                'attr' => ['class' => 'form-control']
            ])
            ->add('lateralidad', ChoiceType::class, [
                'label' => 'Lateralidad',
                'choices' => [
                    'N/A' => 'N/A',
                    'Derecha' => 'Derecha',
                    'Izquierda' => 'Izquierda',
                    'Bilateral' => 'Bilateral',
                ],
                'attr' => ['class' => 'form-select noSrchSelect'],
            ])
            ->add('quirofano', EntityType::class, [
                'class' => Quirofano::class,
                'choice_label' => 'nombre',
                'label' => 'Quirófano (Opcional)',
                'required' => false,
                'placeholder' => '--- Asignar luego ---',
                'attr' => ['class' => 'form-select srchSelect']
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $emergencia = $event->getData();
            if ($emergencia && $emergencia->getPaciente()) {
                $this->addPacienteField($event->getForm(), $emergencia->getPaciente());
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (isset($data['paciente']) && $data['paciente']) {
                $paciente = $this->entityManager->getRepository(Paciente::class)->find($data['paciente']);
                if ($paciente) {
                    $this->addPacienteField($event->getForm(), $paciente);
                }
            }
        });
    }

    private function addPacienteField($form, $paciente): void
    {
        $form->add('paciente', EntityType::class, [
            'class' => Paciente::class,
            'choices' => [$paciente],
            'data' => $paciente,
            'attr' => ['class' => 'form-select ajaxSrchSelect'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cirugia::class,
        ]);
    }
}
