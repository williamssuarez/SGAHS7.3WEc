<?php

namespace App\Form;

use App\Entity\Consulta;
use App\Entity\StatusRecord;
use App\Entity\Vitales;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VitalesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('temperatura')
            ->add('paSistolica')
            ->add('paDiastolica')
            ->add('frecuenciaCardiaca')
            ->add('frecuenciaRespiratoria')
            ->add('spo2')
            ->add('peso')
            ->add('altura')
            ->add('cmb')
            ->add('imc')
            ->add('uidCreate')
            ->add('uidUpdate')
            ->add('created', null, [
                'widget' => 'single_text',
            ])
            ->add('updated', null, [
                'widget' => 'single_text',
            ])
            ->add('consulta', EntityType::class, [
                'class' => Consulta::class,
                'choice_label' => 'id',
            ])
            ->add('status', EntityType::class, [
                'class' => StatusRecord::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vitales::class,
        ]);
    }
}
