<?php

namespace App\DataTable\Type;

use App\Entity\User;
use App\Entity\StatusRecord;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TwigColumn;
use Doctrine\ORM\EntityManagerInterface;

class UserInternalTableType implements DataTableTypeInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('email', TwigColumn::class, [
                'label' => 'Email',
                'template' => 'users/user_internal/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'u.email',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('roles', TwigColumn::class, [
                'label' => 'Roles',
                'template' => 'users/user_internal/_columns.html.twig',
                'className' => 'align-middle',
                'searchable' => false,
                'orderable' => false,
            ])
            ->add('nombre', TwigColumn::class, [
                'label' => 'Nombre',
                'template' => 'users/user_internal/_columns.html.twig',
                'className' => 'align-middle',
                'field' => 'ip.nombre',
                'searchable' => true,
                'globalSearchable' => true,
            ])
            ->add('acciones', TwigColumn::class, [
                'label' => 'Acciones',
                'template' => 'users/user_internal/_columns.html.twig',
                'className' => 'align-middle text-center',
                'searchable' => false,
                'orderable' => false,
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'query' => function (QueryBuilder $builder) {
                    $statusRepo = $this->em->getRepository(StatusRecord::class);
                    $activeStatus = $statusRepo->getActive();
                    $lockedStatus = $statusRepo->getLockedUser();

                    $builder
                        ->select('u')
                        ->from(User::class, 'u')
                        ->leftJoin('u.internalProfile', 'ip')
                        ->where('u.status IN (:statuses)')
                        ->setParameter('statuses', [$activeStatus, $lockedStatus])
                        ->andWhere(
                            $builder->expr()->orX(
                                'CAST(u.roles AS text) LIKE :role_admin',
                                'CAST(u.roles AS text) LIKE :role_receptionist',
                                'CAST(u.roles AS text) LIKE :role_nurse',
                                'CAST(u.roles AS text) LIKE :role_er_nurse',
                                'CAST(u.roles AS text) LIKE :role_doctor',
                                'CAST(u.roles AS text) LIKE :role_er_doctor',
                                'CAST(u.roles AS text) LIKE :role_anesthesiologist',
                                'CAST(u.roles AS text) LIKE :role_surgeon',
                                'CAST(u.roles AS text) LIKE :role_admin_quirofano'
                            )
                        )
                        ->setParameter('role_admin', '%"ROLE_ADMIN"%')
                        ->setParameter('role_receptionist', '%"ROLE_RECEPTIONIST"%')
                        ->setParameter('role_nurse', '%"ROLE_NURSE"%')
                        ->setParameter('role_er_nurse', '%"ROLE_ER_NURSE"%')
                        ->setParameter('role_doctor', '%"ROLE_DOCTOR"%')
                        ->setParameter('role_er_doctor', '%"ROLE_ER_DOCTOR"%')
                        ->setParameter('role_anesthesiologist', '%"ROLE_ANESTHESIOLOGIST"%')
                        ->setParameter('role_surgeon', '%"ROLE_SURGEON"%')
                        ->setParameter('role_admin_quirofano', '%"ROLE_ADMIN_QUIROFANO"%');
                },
            ]);
    }
}
