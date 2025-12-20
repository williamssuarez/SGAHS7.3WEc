<?php

namespace App\Form;

use App\Entity\ExternalProfile;
use App\Entity\InternalProfile;
use App\Entity\StatusRecord;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('isVerified')
            ->add('firstName')
            ->add('lastName')
            ->add('avatarUrl')
            ->add('uidCreate')
            ->add('uidUpdate')
            ->add('created', null, [
                'widget' => 'single_text'
            ])
            ->add('updated', null, [
                'widget' => 'single_text'
            ])
            ->add('internalProfile', EntityType::class, [
                'class' => InternalProfile::class,
                'choice_label' => 'id',
            ])
            ->add('externalProfile', EntityType::class, [
                'class' => ExternalProfile::class,
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
            'data_class' => User::class,
        ]);
    }
}
