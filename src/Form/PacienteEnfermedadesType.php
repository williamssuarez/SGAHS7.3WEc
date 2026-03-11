<?php

namespace App\Form;

use App\Entity\Enfermedades;
use App\Entity\Paciente;
use App\Entity\PacienteEnfermedades;
use App\Enum\PacienteEnfermedadesTipos;
use App\Repository\EnfermedadesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class PacienteEnfermedadesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enfermedad', EntityType::class, [
                'class' => Enfermedades::class,
                'label' => 'Enfermedades',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (EnfermedadesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('fechaDiagnostico', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Diagnostico',
                'label_attr' => [
                    'class' => 'form-label mask'
                ],
                //'model_timezone' => 'UTC',              // Como se guarda en la db
                //'view_timezone' => 'America/Caracas',   // Como la escribe el doctor
                'attr' => [
                    'class' => 'mask form-control',
                    'data-inputmask' => " 'alias': 'datetime', 'clearIncomplete': true, 'inputFormat': 'dd/mm/yyyy' "
                ],
                'required' => true,
                //'data' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
            ])
            ->add('tipo', EnumType::class, [
                'class' => PacienteEnfermedadesTipos::class,
                'label' => 'Tipo de Diagnostico',
                'expanded' => true,
                'multiple' => false,
                'choice_label' => fn (PacienteEnfermedadesTipos $choice) => $choice->getReadableText(),
                'choice_attr' => function(PacienteEnfermedadesTipos $choice) {
                    return [
                        'class' => 'form-check-input'
                    ];
                },
            ])
            ->add('cronica', CheckboxType::class, [
                'label' => '¿Es Cronica?',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'required' => false,
            ])
            ->add('notas', TextareaType::class, [
                'label' => 'Notas/Comentarios Adicionales',
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
            'data_class' => PacienteEnfermedades::class,
        ]);
    }
}
