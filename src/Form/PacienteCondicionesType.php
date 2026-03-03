<?php

namespace App\Form;

use App\Entity\Condiciones;
use App\Entity\Paciente;
use App\Entity\PacienteCondiciones;
use App\Enum\PacienteCondicionesEstados;
use App\Repository\CondicionesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PacienteCondicionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('condicion', EntityType::class, [
                'class' => Condiciones::class,
                'label' => 'Condicion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (CondicionesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('fechaAparicion', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Inicio',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'model_timezone' => 'UTC',              // Como se guarda en la db
                'view_timezone' => 'America/Caracas',   // Como la escribe el doctor
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy' "
                ],
                'required' => true,
                //'data' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
            ])
            ->add('fechaFinalizada', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Finalizacion',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Caracas',
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy' "
                ],
                'row_attr' => [
                    'data-toggle-date-target' => 'endDateContainer',
                    'class' => 'd-none'
                ],
                'required' => false,
            ])
            ->add('estado', EnumType::class, [
                'class' => PacienteCondicionesEstados::class,
                'label' => 'Estado de la Condición',
                'expanded' => true,
                'multiple' => false,
                'choice_label' => fn (PacienteCondicionesEstados $choice) => $choice->getReadableText(),
                'choice_attr' => function(PacienteCondicionesEstados $choice) {
                    return [
                        'class' => 'form-check-input',
                        'data-toggle-date-target' => 'state',
                        'data-action' => 'change->toggle-date#toggle'
                    ];
                },
            ])
            ->add('observaciones', TextareaType::class, [
                'label' => 'Observaciones/Comentarios Adicionales',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PacienteCondiciones::class,
        ]);
    }
}
