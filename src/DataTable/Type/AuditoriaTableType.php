<?php

namespace App\DataTable\Type;

use App\Entity\Audit;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use App\Repository\AuditRepository;

class AuditoriaTableType implements DataTableTypeInterface
{
    private AuditRepository $auditRepository;

    public function __construct(AuditRepository $auditRepository)
    {
        $this->auditRepository = $auditRepository;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('id', TwigColumn::class, [
                'label' => '#',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.id',
                'searchable' => false,
                'globalSearchable' => false,
            ])
            ->add('tipoAudit', TwigColumn::class, [
                'label' => 'Tipo',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.tipoAudit',
                'searchable' => false,
            ])
            ->add('descripcion', TwigColumn::class, [
                'label' => 'Detalles',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.descripcion',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('direccionIp', TwigColumn::class, [
                'label' => 'Dirección IP',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.direccionIp',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('usuario', TwigColumn::class, [
                'label' => 'Usuario',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.uidCreate',
                'searchable' => false,
            ])
            ->add('fecha', TwigColumn::class, [
                'label' => 'Fecha',
                'template' => 'auditoria/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.created',
                'searchable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Audit::class,
                'query' => function (QueryBuilder $builder) use ($options) {
                    $startDate = $options['startDate'];
                    $endDate = $options['endDate'];
                    $state = $options['state'];
                    $userId = $options['userId'];

                    if ($state == 'all') {
                        $this->auditRepository->createDataTablesDateOnlyQueryBuilder($builder, $startDate, $endDate, $userId);
                    } else {
                        $this->auditRepository->createDataTablesStateQueryBuilder($builder, $state, $startDate, $endDate, $userId);
                    }
                },
            ]);
    }
}
