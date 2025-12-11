<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use App\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneType extends AbstractType
{
    private $transformer;

    public function __construct(PhoneNumberTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prefix', ChoiceType::class, [
                'choices'  => [
                    '0412' => '0412',
                    '0414' => '0414',
                    '0416' => '0416',
                    '0422' => '0422',
                    '0424' => '0424',
                    '0426' => '0426',
                ],
                'label' => false,
                'attr' => ['class' => 'noSrchSelect']
            ])
            ->add('number', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control number-only',
                    'maxlength' => '7'
                ],
                'required' => true,
            ]);

        // Apply the transformer to this form type
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //'data_class' => null,
        ]);
    }
}
