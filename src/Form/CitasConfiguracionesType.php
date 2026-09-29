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
            ->add('maxPacientesDia', NumberType::class, [
                'label' => 'Pacientes Maximos por Dia',
                'label_attr' => [
                    'class' => 'form-label number-only'
                ],
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxLength' => 4,
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar el maximo de pacientes a atender.'),
                ]
            ])
            ->add('descripcion', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => 'Descripción (Opcional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Horario de invierno para mayor demanda',
                    'rows' => 2
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
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debe ingresar la duracion promedio por cita.'),
                ]
            ])
            ->add('tieneTiempoReceso', CheckboxType::class, [
                'label' => '¿Incluir tiempo de receso entre citas?',
                'label_attr' => ['class' => 'form-check-label'],
                'attr' => [
                    'class' => 'form-check-input bigCheckbox',
                    'data-conditional-field-target' => 'trigger',
                    'data-action' => 'change->conditional-field#toggle',
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
