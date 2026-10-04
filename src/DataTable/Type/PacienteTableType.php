<?php

namespace App\DataTable\Type;

use App\Entity\Paciente;
use App\Repository\PacienteRepository;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Doctrine\ORM\QueryBuilder;

class PacienteTableType implements DataTableTypeInterface
{
    private PacienteRepository $pacienteRepository;

    public function __construct(PacienteRepository $pacienteRepository)
    {
        $this->pacienteRepository = $pacienteRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('foto', TwigColumn::class, [
                'label' => '#',
                'template' => 'paciente/_table_columns.html.twig',
                'className' => 'align-middle text-center',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('nombre', TextColumn::class, [
                'label' => 'Nombre',
                'className' => 'align-middle',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('apellido', TextColumn::class, [
                'label' => 'Apellido',
                'className' => 'align-middle',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('cedula', TwigColumn::class, [
                'label' => 'Cédula',
                'template' => 'paciente/_table_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'p.cedula',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('telefono', TextColumn::class, [
                'label' => 'Teléfono',
                'className' => 'align-middle',
            ])
            ->add('correo', TextColumn::class, [
                'label' => 'Correo',
                'className' => 'align-middle',
            ])
            ->add('direccion', TextColumn::class, [
                'label' => 'Dirección',
                'className' => 'align-middle',
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'paciente/_table_columns.html.twig',
                'className' => 'align-middle text-center',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Paciente::class,
                'query' => function (QueryBuilder $builder) {
                    $this->pacienteRepository->createDataTablesQueryBuilder($builder);
                },
            ]);
    }
}
