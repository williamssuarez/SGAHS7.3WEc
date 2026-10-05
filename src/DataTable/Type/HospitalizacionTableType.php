<?php

namespace App\DataTable\Type;

use App\Entity\Hospitalizaciones;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\HospitalizacionesRepository;

class HospitalizacionTableType implements DataTableTypeInterface
{
    private HospitalizacionesRepository $hospitalizacionRepository;

    public function __construct(HospitalizacionesRepository $hospitalizacionRepository)
    {
        $this->hospitalizacionRepository = $hospitalizacionRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('id', TwigColumn::class, [
                'label' => 'ID',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'h.id',
                'searchable' => false,
                'globalSearchable' => false,
            ])
            ->add('fechaIngreso', TwigColumn::class, [
                'label' => 'Fecha Ingreso',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'h.fechaIngreso',
                'searchable' => false,
            ])
            ->add('paciente', TwigColumn::class, [
                'label' => 'Paciente',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'pac.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('pacienteApellido', TwigColumn::class, [
                'label' => 'Paciente Apellido',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'field' => 'pac.apellido',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('pacienteCedula', TwigColumn::class, [
                'label' => 'Paciente Cedula',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'field' => 'pac.cedula',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('medicoTratante', TwigColumn::class, [
                'label' => 'Medico Tratante',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'medProfile.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('estado', TwigColumn::class, [
                'label' => 'Estado',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'h.estado',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('accion', TwigColumn::class, [
                'label' => 'Accion',
                'template' => 'hospitalizaciones/_columns.html.twig',
                'className' => 'align-middle text-end',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Hospitalizaciones::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];
                    $state = $options['state'] ?? 'all';

                    if ($state == 'all') {
                        $this->hospitalizacionRepository->createDataTablesDateOnlyQueryBuilder($builder, $startDate, $endDate);
                    } else {
                        $this->hospitalizacionRepository->createDataTablesStateQueryBuilder($builder, $state, $startDate, $endDate);
                    }

                    // Joins for search fields
                    $builder
                        ->leftJoin('h.paciente', 'pac')
                        ->leftJoin('h.medicoTratante', 'med')
                        ->leftJoin('med.internalProfile', 'medProfile');
                },
            ]);
    }
}

