<?php

namespace App\Form;

use App\Entity\Cama;
use App\Entity\Emergencia;
use App\Entity\HospitalizacionCama;
use App\Entity\Hospitalizaciones;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsignarCamaHospitalizacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('camaActual', EntityType::class, [
                'class' => HospitalizacionCama::class,
                'label' => 'Seleccione una Cama Disponible',
                'placeholder' => '--- Elija una cama ---',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->join('c.habitacion', 'z')
                        ->where('c.estado = :estado')
                        ->setParameter('estado', 'available')
                        ->orderBy('z.nombre', 'ASC')
                        ->addOrderBy('c.codigo', 'ASC');
                },
                'group_by' => function(HospitalizacionCama $cama) {
                    return $cama->getHabitacion()->getNombre();
                },
                'choice_label' => 'codigo',
                'attr' => ['class' => 'form-select']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hospitalizaciones::class,
        ]);
    }
}
