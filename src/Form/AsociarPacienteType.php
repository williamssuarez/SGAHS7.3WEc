<?php

namespace App\Form;

use App\Entity\Emergencia;
use App\Entity\Paciente;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsociarPacienteType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('paciente', EntityType::class, [
            'class' => Paciente::class,
            'label' => 'Vincular Paciente Oficial',
            'placeholder' => 'Buscar por Cédula o Nombre...',
            'choices' => [], // Empty by default for AJAX
            'attr' => ['class' => 'form-select ajaxSrchSelect w-100'],
            'required' => true,
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
            'attr' => ['class' => 'form-select ajaxSrchSelect w-100'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Emergencia::class]);
    }
}
