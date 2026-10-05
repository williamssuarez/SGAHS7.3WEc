<?php

namespace App\DataTable\Type;

use App\Entity\Citas;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\CitasRepository;

class CitasTableType implements DataTableTypeInterface
{
    private CitasRepository $citasRepository;

    public function __construct(CitasRepository $citasRepository)
    {
        $this->citasRepository = $citasRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('especialidad', TwigColumn::class, [
                'label' => 'Especialidad',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'esp.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('doctor', TwigColumn::class, [
                'label' => 'Doctor',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'doc.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('consultorio', TwigColumn::class, [
                'label' => 'Consultorio',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'con.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('paciente', TwigColumn::class, [
                'label' => 'Paciente',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'pac.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('fecha', TwigColumn::class, [
                'label' => 'Fecha',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.fecha',
                'searchable' => false,
            ])
            ->add('horario', TwigColumn::class, [
                'label' => 'Inicio-Fin',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('estado', TwigColumn::class, [
                'label' => 'Estado',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'citas/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Citas::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];
                    $state = $options['state'] ?? 'all';

                    if ($state == 'all') {
                        $this->citasRepository->createDataTablesDateOnlyQueryBuilder($builder, $startDate, $endDate);
                    } else {
                        $this->citasRepository->createDataTablesStateQueryBuilder($builder, $state, $startDate, $endDate);
                    }

                    // Joins for the searching fields
                    $builder
                        ->leftJoin('u.especialidad', 'esp')
                        ->leftJoin('u.doctor', 'doc')
                        ->leftJoin('u.consultorio', 'con')
                        ->leftJoin('u.paciente', 'pac');
                },
            ]);
    }
}
