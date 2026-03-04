<?php

namespace App\Form;

use App\Entity\Inmunizaciones;
use App\Entity\Paciente;
use App\Entity\PacienteInmunizaciones;
use App\Entity\Reacciones;
use App\Enum\PacienteInmunizacionesDosis;
use App\Repository\InmunizacionesRepository;
use App\Repository\ReaccionesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PacienteInmunizacionesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inmunizacion', EntityType::class, [
                'class' => Inmunizaciones::class,
                'label' => 'Inmunizacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (InmunizacionesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
            ->add('dosis', EnumType::class, [
                'class' => PacienteInmunizacionesDosis::class,
                'label' => 'Dosis',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'noSrchSelect'
                ],
                'expanded' => false,
                'required' => true,
                'choice_label' => fn (PacienteInmunizacionesDosis $choice) => $choice->getReadableText(),
            ])
            ->add('fechaAplicacion', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de Aplicacion',
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
            ->add('sitioAplicacion', TextType::class, [
                'label' => 'Sitio de Aplicacion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Muslo derecho'
                ],
                'required' => false,
            ])
            ->add('fabricante', TextType::class, [
                'label' => 'Fabricante',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Sanofi'
                ],
                'required' => false,
            ])
            ->add('reacciones', EntityType::class, [
                'class' => Reacciones::class,
                'label' => 'Reacciones',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_label' => 'nombre',
                'multiple' => true,
                'attr' => [
                    'class' => 'srchSelect'
                ],
                'required' => true,
                'query_builder' => function (ReaccionesRepository $er) {
                    return $er->getActivesforSelect();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PacienteInmunizaciones::class,
        ]);
    }
}
