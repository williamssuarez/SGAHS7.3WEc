<?php

namespace App\DataTable\Type;

use App\Entity\VisitaHospitalaria;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\VisitaHospitalariaRepository;

class VisitaHospitalariaTableType implements DataTableTypeInterface
{
    private VisitaHospitalariaRepository $visitaRepository;

    public function __construct(VisitaHospitalariaRepository $visitaRepository)
    {
        $this->visitaRepository = $visitaRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('visitante', TwigColumn::class, [
                'label' => 'Visitante',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'v.nombreVisitante',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('identificacionVisitante', TwigColumn::class, [
                'label' => 'Visitante CI',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'field' => 'v.identificacionVisitante',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('parentesco', TwigColumn::class, [
                'label' => 'Parentesco',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'v.parentesco',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('paciente', TwigColumn::class, [
                'label' => 'Visitando a (Paciente)',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'p.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('pacienteApellido', TwigColumn::class, [
                'label' => 'Paciente Apellido',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'field' => 'p.apellido',
                'visible' => false,
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('ubicacion', TwigColumn::class, [
                'label' => 'Ubicación',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('horaEntrada', TwigColumn::class, [
                'label' => 'Hora Entrada',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'v.fechaHoraEntrada',
                'searchable' => false,
            ])
            ->add('horaSalida', TwigColumn::class, [
                'label' => 'Hora Salida',
                'template' => 'visita_hospitalaria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'v.fechaHoraSalida',
                'searchable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => VisitaHospitalaria::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];

                    $this->visitaRepository->createDataTablesDateOnlyQueryBuilder($builder, $startDate, $endDate);

                    // Eager loading & Joins for search fields
                    $builder
                        ->leftJoin('v.hospitalizacion', 'h')
                        ->leftJoin('h.paciente', 'p')
                        ->leftJoin('h.camaActual', 'c')
                        ->leftJoin('c.habitacion', 'hab')
                        ->leftJoin('hab.area', 'a')
                        ->addSelect('h', 'p', 'c', 'hab', 'a');
                },
            ]);
    }
}
