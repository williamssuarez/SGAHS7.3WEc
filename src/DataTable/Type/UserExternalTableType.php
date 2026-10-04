<?php

namespace App\DataTable\Type;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TwigColumn;

class UserExternalTableType implements DataTableTypeInterface
{
    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('email', TwigColumn::class, [
                'label' => 'Email',
                'template' => 'users/user_external/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.email',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('nombre', TwigColumn::class, [
                'label' => 'Nombre',
                'template' => 'users/user_external/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'ep.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('status', TwigColumn::class, [
                'label' => 'Estado',
                'template' => 'users/user_external/_columns.html.twig',
                'className' => 'align-middle text-center',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'users/user_external/_columns.html.twig',
                'className' => 'align-middle text-center',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('u')
                        ->from(User::class, 'u')
                        ->leftJoin('u.externalProfile', 'ep')
                        ->where('CAST(u.roles AS text) LIKE :role_external')
                        ->setParameter('role_external', '%"ROLE_EXTERNAL"%');
                },
            ]);
    }
}
