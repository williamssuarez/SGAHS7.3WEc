<?php

namespace App\Form;

use App\Entity\CitasConfiguraciones;
use App\Entity\Consultorios;
use App\Entity\Especialidades;
use App\Entity\StatusRecord;
use App\Repository\ConsultoriosRepository;
use App\Repository\EspecialidadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CitasConfiguracionesType extends AbstractType
{
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
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (EspecialidadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('consultorio', EntityType::class, [
                'class' => Consultorios::class,
                'label' => 'Consultorios',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect',
                    'data-capacity-validator-target' => 'consultorios'
                ],
                'required' => true,
                'query_builder' => function (ConsultoriosRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('horaInicio', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de Inicio',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'hh:ii' ",
                    'data-capacity-validator-target' => 'inicio',
                    'data-action' => 'input->capacity-validator#calculate'
                ],
                'required' => true,
            ])
            ->add('horaFin', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora de Cierre',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'hh:ii' ",
                    'data-capacity-validator-target' => 'fin',
                    'data-action' => 'input->capacity-validator#calculate'
                ],
                'required' => true,
            ])
            ->add('maxPacientesDia', NumberType::class, [
                'label' => 'Pacientes Maximos por Dia',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 4,
                    'data-capacity-validator-target' => 'max',
                    'data-action' => 'input->capacity-validator#calculate'
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el maximo de pacientes a atender.'),
                ]
            ])
            ->add('tieneEdadPrioridad', CheckboxType::class, [
                'label' => '¿Tiene prioridad de edad?',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => [
                    'class' => 'form-check-input bigCheckbox',
                    'data-conditional-field-target' => 'trigger',
                    'data-action' => 'change->conditional-field#toggle'
                ],
                'required' => false,
            ])
            ->add('edadPrioridad', NumberType::class, [
                'label' => 'Edad a priorizar',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 4,
                ],
                'required' => false,
            ])
            ->add('duracionCita', NumberType::class, [
                'label' => 'Duracion promedio por cita (en minutos)',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 4,
                    'data-capacity-validator-target' => 'duracion',
                    'data-action' => 'input->capacity-validator#calculate'
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la duracion promedio por cita.'),
                ]
            ])
            ->add('diasSemana', ChoiceType::class, [
                'label' => 'Días de Atención',
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
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
            ])
            ->add('tieneTiempoReceso', CheckboxType::class, [
                'label' => '¿Incluir tiempo de receso entre citas?',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => [
                    'class' => 'form-check-input bigCheckbox',
                    'data-conditional-field-target' => 'trigger',
                    'data-action' => 'change->conditional-field#toggle change->capacity-validator#calculate', // Note the multiple actions!
                    'data-capacity-validator-target' => 'tieneReceso'
                ],
                'required' => false,
            ])
            ->add('tiempoReceso', NumberType::class, [
                'label' => 'Minutos de receso',
                'label_attr' => ['class' => 'form-label number-only'],
                'attr' => [
                    'class' => 'form-control number-only',
                    'placeholder' => 'Ej: 5, 10...',
                    'maxLength' => 2,
                    'data-capacity-validator-target' => 'receso',
                    'data-action' => 'input->capacity-validator#calculate'
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitasConfiguraciones::class,
        ]);
    }
}
