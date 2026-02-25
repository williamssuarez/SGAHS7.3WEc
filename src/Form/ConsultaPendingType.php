<?php

namespace App\Form;

use App\Entity\Consulta;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Entity\Vitales;
use App\Enum\ConsultaTipos;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultaPendingType extends AbstractType
{
    private $entityManager;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tipoConsulta', EnumType::class, [
                'label' => 'Tipo de Consulta',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'class' => ConsultaTipos::class,
                'choice_label' => fn (ConsultaTipos $choice) => $choice->getReadableText(),
                'attr' => ['class' => 'noSrchSelect'],
                'placeholder' => 'Seleccione...',
                'expanded' => false, // false = dropdown, true = radio buttons
                'multiple' => false, // true = multiple selection
            ])
            ->add('fechaInicio', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Inicio',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'with_seconds' => false,
                'model_timezone' => 'UTC',              // Como se guarda en la db
                'view_timezone' => 'America/Caracas',   // Como la escribe el doctor
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy HH:MM' "
                ],
                'required' => true,
                //'data' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
            ])
            ->add('paciente', EntityType::class, [
                'class' => Paciente::class,
                'label' => 'Paciente',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'placeholder' => 'Buscar paciente...',
                'choices' => [],
                'attr' => ['class' => 'ajaxSrchSelect'],
                'required' => true,
            ])

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $consulta = $event->getData();
                $form = $event->getForm();

                if ($consulta && $consulta->getPaciente()) {
                    // If we are editing, we add the current patient as the ONLY choice
                    $this->addPacienteField($form, $consulta->getPaciente());
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

    private function addFechaInicioField($form, ?\DateTime $fechaInicio)
    {
        $form->add('fechaInicio', DateTimeType::class, [
            'widget' => 'single_text',
            'label' => 'Fecha de Inicio',
            'label_attr' => [
                'class' => 'form-label mask'
            ],
            'with_seconds' => false,
            'attr' => [
                'class' => 'mask form-control',
                'data-inputmask' => " 'alias': 'date', 'clearIncomplete': true "
            ],
            'required' => true,
            'data' => $fechaInicio,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consulta::class,
        ]);
    }
}
