<?php

namespace App\DataTable\Type;

use App\Entity\Cirugia;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\CirugiaRepository;

class CirugiaTableType implements DataTableTypeInterface
{
    private CirugiaRepository $cirugiaRepository;

    public function __construct(CirugiaRepository $cirugiaRepository)
    {
        $this->cirugiaRepository = $cirugiaRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('fechaHora', TwigColumn::class, [
                'label' => 'Fecha y Hora',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'c.fechaHoraProgramada',
                'searchable' => false,
            ])
            ->add('paciente', TwigColumn::class, [
                'label' => 'Paciente',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'p.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('pacienteApellido', TwigColumn::class, [
                'label' => 'Paciente Apellido',
                'template' => 'cirugia/_columns.html.twig',
                'field' => 'p.apellido',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('cedula', TwigColumn::class, [
                'label' => 'Cédula',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'p.cedula',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('procedimiento', TwigColumn::class, [
                'label' => 'Procedimiento',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'c.procedimientoPropuesto',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('cirujano', TwigColumn::class, [
                'label' => 'Cirujano',
                'template' => 'cirugia/_columns.html.twig',
                'field' => 'm.nombre',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('quirofano', TwigColumn::class, [
                'label' => 'Quirófano',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'q.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('estado', TwigColumn::class, [
                'label' => 'Estado',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'cirugia/_columns.html.twig',
                'className' => 'align-middle text-end',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Cirugia::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];
                    $estado = $options['estado'];

                    $this->cirugiaRepository->createDataTablesQueryBuilder($builder, $startDate, $endDate, $estado);

                    // Eager loading & Joins for search fields
                    $builder
                        ->leftJoin('c.paciente', 'p')
                        ->leftJoin('c.quirofano', 'q')
                        ->leftJoin('c.cirujanoPrincipal', 'm')
                        
                        ->addSelect('p', 'q', 'm');
                },
            ]);
    }
}

