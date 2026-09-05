<?php

namespace App\Form\Type;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Entity\Sector;
use App\Repository\MunicipioRepository;
use App\Repository\ParroquiaRepository;
use App\Repository\SectorRepository;
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
        // 1. Static Base Fields (Identical to before)
        $builder
            ->add('estado', EntityType::class, [
                'class' => Estado::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => 'Seleccione un Estado',
                'attr' => ['data-cascading-location-target' => 'estado', 'class' => 'srchSelect']
            ])
            ->add('municipio', EntityType::class, [
                'class' => Municipio::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => 'Seleccione un Estado primero',
                'choices' => [],
                'attr' => ['disabled' => true, 'data-cascading-location-target' => 'municipio', 'class' => 'srchSelect']
            ])
            ->add('parroquia', EntityType::class, [
                'class' => Parroquia::class,
                'choice_label' => 'nombre',
                'mapped' => false,
                'placeholder' => 'Seleccione un Municipio primero',
                'choices' => [],
                'attr' => ['disabled' => true, 'data-cascading-location-target' => 'parroquia', 'class' => 'srchSelect']
            ])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Seleccione una Parroquia primero',
                'choices' => [],
                'attr' => ['disabled' => true, 'data-cascading-location-target' => 'sector', 'class' => 'srchSelect']
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);

        // 2. NEW: Manually push the selected sector back to the parent entity
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $parentData = $form->getParent()?->getData();
            $selectedSector = $form->get('sector')->getData();

            // As long as the parent entity (Paciente, UserExternal, etc.) has a setSector method, this works!
            if ($parentData && $selectedSector && method_exists($parentData, 'setSector')) {
                $parentData->setSector($selectedSector);
            }
        });
    }

    protected function addElements(FormInterface $form, ?Estado $estado, ?Municipio $municipio, ?Parroquia $parroquia, ?Sector $sector)
    {
        $frmEstOptions = [
            'class' => Estado::class,
            'placeholder' => 'Seleccione un Estado',
            'choice_label' => 'nombre',
            'choice_value' => 'id',
            'mapped' => false,
            'required' => true,
            'attr' => ['data-cascading-location-target' => 'estado', 'class' => 'srchSelect']
        ];
        if ($estado) { $frmEstOptions['data'] = $estado; }
        $form->add('estado', EntityType::class, $frmEstOptions);

        $frmMunOptions = [
            'class' => Municipio::class,
            'choice_label' => 'nombre',
            'choice_value' => 'id',
            'mapped' => false,
            'required' => true,
            'placeholder' => $estado ? 'Seleccione un Municipio' : 'Seleccione un Estado primero',
            'attr' => ['disabled' => $estado === null, 'data-cascading-location-target' => 'municipio', 'class' => 'srchSelect'],
            'query_builder' => fn (MunicipioRepository $er) => $er->getByEstadoQueryBuilder($estado)
        ];
        if ($municipio) { $frmMunOptions['data'] = $municipio; }
        $form->add('municipio', EntityType::class, $frmMunOptions);

        $frmPrrOptions = [
            'class' => Parroquia::class,
            'choice_label' => 'nombre',
            'choice_value' => 'id',
            'mapped' => false,
            'required' => true,
            'placeholder' => $municipio ? 'Seleccione una Parroquia' : 'Seleccione un Municipio primero',
            'attr' => ['disabled' => $municipio === null, 'data-cascading-location-target' => 'parroquia', 'class' => 'srchSelect'],
            'query_builder' => fn (ParroquiaRepository $er) => $er->getByMunicipioQueryBuilder($municipio)
        ];
        if ($parroquia) { $frmPrrOptions['data'] = $parroquia; }
        $form->add('parroquia', EntityType::class, $frmPrrOptions);

        $frmSectOptions = [
            'class' => Sector::class,
            'choice_label' => 'nombre',
            'choice_value' => 'id',
            'mapped' => false, // Ensure this is also false so our POST_SUBMIT handles it cleanly
            'required' => true,
            'placeholder' => $parroquia ? 'Seleccione un Sector' : 'Seleccione una Parroquia primero',
            'attr' => ['disabled' => $parroquia === null, 'data-cascading-location-target' => 'sector', 'class' => 'srchSelect'],
            'query_builder' => fn (SectorRepository $er) => $er->getByParroquiaQueryBuilder($parroquia)
        ];
        if ($sector) { $frmSectOptions['data'] = $sector; }
        $form->add('sector', EntityType::class, $frmSectOptions);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();

        // 3. NEW: Look upwards to the parent form (e.g., PacienteType) to find the saved data
        $parentData = $form->getParent()?->getData();

        $sector = ($parentData && method_exists($parentData, 'getSector')) ? $parentData->getSector() : null;
        $parroquia = $sector ? $sector->getParroquia() : null;
        $municipio = $parroquia ? $parroquia->getMunicipio() : null;
        $estado = $municipio ? $municipio->getEstado() : null;

        $this->addElements($form, $estado, $municipio, $parroquia, $sector);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!$data) return;

        $estadoId = $data['estado'] ?? null;
        $municipioId = $data['municipio'] ?? null;
        $parroquiaId = $data['parroquia'] ?? null;
        $sectorId = $data['sector'] ?? null;

        $estado = $estadoId ? $this->em->getRepository(Estado::class)->find($estadoId) : null;
        $municipio = $municipioId ? $this->em->getRepository(Municipio::class)->find($municipioId) : null;
        $parroquia = $parroquiaId ? $this->em->getRepository(Parroquia::class)->find($parroquiaId) : null;
        $sector = $sectorId ? $this->em->getRepository(Sector::class)->find($sectorId) : null;

        $this->addElements($form, $estado, $municipio, $parroquia, $sector);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 4. NEW: Destroy inherit_data. The child form is now entirely autonomous.
            'mapped' => false,
        ]);
    }
}
