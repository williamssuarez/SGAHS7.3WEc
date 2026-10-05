<?php

namespace App\DataTable\Type;

use App\Entity\Emergencia;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\EmergenciaRepository;

class EmergenciaTableType implements DataTableTypeInterface
{
    private EmergenciaRepository $emergenciaRepository;

    public function __construct(EmergenciaRepository $emergenciaRepository)
    {
        $this->emergenciaRepository = $emergenciaRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('ingreso', TwigColumn::class, [
                'label' => 'Ingreso',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.fechaIngreso',
                'searchable' => false,
            ])
            ->add('egreso', TwigColumn::class, [
                'label' => 'Egreso',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'a.fechaEgreso',
                'searchable' => false,
            ])
            ->add('paciente', TwigColumn::class, [
                'label' => 'Paciente',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'pac.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('pacienteTemporal', TwigColumn::class, [
                'label' => 'Paciente Temporal',
                'template' => 'emergencia/_columns.html.twig',
                'field' => 'u.pacienteTemporal',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('prioridad', TwigColumn::class, [
                'label' => 'Prioridad',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 't.prioridad',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('condicionAlta', TwigColumn::class, [
                'label' => 'Condición de Alta',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'a.condicionAlta',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('diagnosticoFinal', TwigColumn::class, [
                'label' => 'Diagnóstico Final',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'a.diagnosticoFinal',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'emergencia/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Emergencia::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];
                    $state = $options['state'] ?? 'all';

                    if ($state == 'all') {
                        $this->emergenciaRepository->createDataTablesDateOnlyQueryBuilder($builder, $startDate, $endDate);
                    } else {
                        $this->emergenciaRepository->createDataTablesStateQueryBuilder($builder, $state, $startDate, $endDate);
                    }

                    // For search/sort fields:
                    // Note: getActivesforTableByState already joins u.altaMedica as 'a'.
                    // So we only join 'a' if we are in 'all' state.
                    if ($state == 'all') {
                        $builder->leftJoin('u.altaMedica', 'a');
                    }
                    
                    $builder
                        ->leftJoin('u.paciente', 'pac')
                        ->leftJoin('u.triage', 't');
                },
            ]);
    }
}
