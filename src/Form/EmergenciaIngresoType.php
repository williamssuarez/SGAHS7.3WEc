<?php

namespace App\Form;

use App\Entity\Cama;
use App\Entity\Emergencia;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmergenciaIngresoType extends AbstractType
{
    private $entityManager;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paciente', EntityType::class, [
                'class' => Paciente::class,
                'label' => 'Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'placeholder' => 'Buscar paciente...',
                'choices' => [],
                'attr' => ['class' => 'ajaxSrchSelect'],
                'required' => false,
            ])
            ->add('pacienteTemporal', TextType::class, [
                'label' => 'Nombre Temporal (En caso de no poder identificar)',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Hombre alto de lentes...',
                ],
                'required' => false,
            ])

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $emergencia = $event->getData();
                $form = $event->getForm();

                if ($emergencia && $emergencia->getPaciente()) {
                    // If we are editing, we add the current patient as the ONLY choice
                    $this->addPacienteField($form, $emergencia->getPaciente());
                }
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                if (isset($data['paciente']) && $data['paciente']) {
                    $paciente = $this->entityManager->getRepository(Paciente::class)->find($data['paciente']);

                    if ($paciente) {
                        $this->addPacienteField($form, $paciente);
                    }
                }
            })
        ;
    }

    private function addPacienteField($form, ?Paciente $paciente = null)
    {
        $form->add('paciente', EntityType::class, [
            'class' => Paciente::class,
            'placeholder' => 'Buscar paciente...',
            'choices' => $paciente ? [$paciente] : [],
            'attr' => ['class' => 'ajaxSrchSelect'],
            'required' => true,
            'label' => 'Paciente',
            'label_attr' => ['class' => 'form-label'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emergencia::class,
        ]);
    }
}
