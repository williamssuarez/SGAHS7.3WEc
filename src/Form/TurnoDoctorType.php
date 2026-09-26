<?php

namespace App\Form;

use App\Entity\Especialidades;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use App\Entity\TurnoDoctor;
use App\Repository\EspecialidadesRepository;
use App\Repository\InternalProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TurnoDoctorType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('especialidad', EntityType::class, [
                'class' => Especialidades::class,
                'label' => 'Especialidad',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'data-cascading-doctor-target' => 'especialidad',
                    'class' => 'srchSelect'
                ],
                //'data' => $especialidad,
                'required' => true,
                'query_builder' => function (EspecialidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('dayOfWeek', ChoiceType::class, [
                'label' => 'Día de la Semana',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choices' => [
                    'Lunes' => 1,
                    'Martes' => 2,
                    'Miércoles' => 3,
                    'Jueves' => 4,
                    'Viernes' => 5,
                    'Sábado' => 6,
                    'Domingo' => 7,
                ],
                'required' => true,
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de Inicio',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'hh:ii' ",
                ],
                'required' => true,
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de Cierre',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'hh:ii' ",
                ],
                'required' => true,
            ])
            ->add('slotDuration', NumberType::class, [
                'label' => 'Duracion promedio por cita (en minutos)',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 4,
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la duracion promedio por cita.'),
                ]
            ])
        ;

        // 2. The Modifier (ONLY recreates the dynamic doctor field)
        $formModifier = function (FormInterface $form, ?Especialidades $especialidad = null) {
            $form->add('doctor', EntityType::class, [
                'class' => InternalProfile::class,
                'choice_label' => fn (InternalProfile $p) => $p->getNombre() . ' ' . $p->getApellido(),
                'choice_value' => 'id',
                'placeholder' => $especialidad ? 'Seleccione un Doctor' : 'Seleccione una Especialidad primero',
                'attr' => [
                    'disabled' => $especialidad === null,
                    'data-cascading-doctor-target' => 'doctor',
                    'class' => 'srchSelect'
                ],
                'query_builder' => fn (InternalProfileRepository $er) => $er->getDoctorsByEspecialidadQueryBuilder($especialidad)
            ]);
        };

        // 3. Lifecycle Events
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $turno = $event->getData();
            $especialidad = $turno ? $turno->getEspecialidad() : null;

            $formModifier($event->getForm(), $especialidad);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            if (!$data) return;

            $especialidadId = $data['especialidad'] ?? null;
            $especialidad = $especialidadId ? $this->em->getRepository(Especialidades::class)->find($especialidadId) : null;

            $formModifier($event->getForm(), $especialidad);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TurnoDoctor::class,
        ]);
    }
}
