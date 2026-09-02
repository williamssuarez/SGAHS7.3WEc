<?php

namespace App\Form\Type;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationSelectorType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // 1. Static base fields (Guarantees they always exist for Twig so it never crashes)
        $builder->add('estado', EntityType::class, [
            'class' => Estado::class,
            'choice_label' => 'nombre',
            'mapped' => false,
            'placeholder' => 'Seleccione un Estado',
            'attr' => [
                'data-action' => 'change->cascading-location#updateMunicipios',
                'data-cascading-location-target' => 'estado',
                'class' => 'srchSelect',
            ]
        ])
            ->add('municipio', EntityType::class, [
                'class' => Municipio::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => 'Seleccione un Estado primero',
                'choices' => [], // Static empty array
                'attr' => [
                    'disabled' => true,
                    'data-action' => 'change->cascading-location#updateParroquias',
                    'data-cascading-location-target' => 'municipio',
                    'class' => 'srchSelect',
                ]
            ])
            ->add('parroquia', EntityType::class, [
                'class' => Parroquia::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => 'Seleccione un Municipio primero',
                'choices' => [],
                'attr' => [
                    'disabled' => true,
                    'data-action' => 'change->cascading-location#updateSectores',
                    'data-cascading-location-target' => 'parroquia',
                    'class' => 'srchSelect',
                ]
            ])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccione una Parroquia primero',
                'choices' => [],
                'attr' => [
                    'disabled' => true,
                    'data-cascading-location-target' => 'sector',
                    'class' => 'srchSelect',
                ]
            ]);

        // 2. The Modifier (ONLY updates the choices and disabled state. No volatile 'data' overrides!)
        $formModifier = function (FormInterface $form, ?Estado $estado, ?Municipio $municipio, ?Parroquia $parroquia) {
            $form->add('municipio', EntityType::class, [
                'class' => Municipio::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => $estado ? 'Seleccione un Municipio' : 'Seleccione un Estado primero',
                'choices' => $estado ? $estado->getMunicipios() : [],
                'attr' => [
                    'disabled' => $estado === null,
                    'data-action' => 'change->cascading-location#updateParroquias',
                    'data-cascading-location-target' => 'municipio',
                    'class' => 'srchSelect',
                ]
            ]);

            $form->add('parroquia', EntityType::class, [
                'class' => Parroquia::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => $municipio ? 'Seleccione una Parroquia' : 'Seleccione un Municipio primero',
                'choices' => $municipio ? $municipio->getParroquias() : [],
                'attr' => [
                    'disabled' => $municipio === null,
                    'data-action' => 'change->cascading-location#updateSectores',
                    'data-cascading-location-target' => 'parroquia',
                    'class' => 'srchSelect',
                ]
            ]);

            $form->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'nombre',
                'placeholder' => $parroquia ? 'Seleccione un Sector' : 'Seleccione una Parroquia primero',
                'choices' => $parroquia ? $parroquia->getSectores() : [],
                'attr' => [
                    'disabled' => $parroquia === null,
                    'data-cascading-location-target' => 'sector',
                    'class' => 'srchSelect',
                ]
            ]);
        };

        // 3. POST_SET_DATA: Safely forces data into the unmapped fields AFTER the form is initialized
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $entity = $event->getData();
            $form = $event->getForm();

            // If it's a new record or has no sector, do nothing. The static placeholders will render.
            if (!$entity || !$entity->getSector()) return;

            // Retrieve the saved location tree
            $sector = $entity->getSector();
            $parroquia = $sector->getParroquia();
            $municipio = $parroquia->getMunicipio();
            $estado = $municipio->getEstado();

            // 1. Rebuild the fields to inject the actual database choices
            $formModifier($form, $estado, $municipio, $parroquia);

            // 2. Force the data onto the unmapped fields so the HTML renders <option selected>
            $form->get('estado')->setData($estado);
            $form->get('municipio')->setData($municipio);
            $form->get('parroquia')->setData($parroquia);

            // (Note: 'sector' is a mapped field, so Symfony's DataMapper has already set its value automatically!)
        });

        // 4. PRE_SUBMIT: Rebuilds fields using incoming POST IDs to ensure Symfony validation passes
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data) return;

            $estado = isset($data['estado']) && $data['estado'] ? $this->em->getRepository(Estado::class)->find($data['estado']) : null;
            $municipio = isset($data['municipio']) && $data['municipio'] ? $this->em->getRepository(Municipio::class)->find($data['municipio']) : null;
            $parroquia = isset($data['parroquia']) && $data['parroquia'] ? $this->em->getRepository(Parroquia::class)->find($data['parroquia']) : null;

            $formModifier($form, $estado, $municipio, $parroquia);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
