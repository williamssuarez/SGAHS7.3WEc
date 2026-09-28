<?php

namespace App\Controller;

use App\Service\AuditService;
use App\Service\DatabaseBackupService;
use App\Enum\AuditTipos;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/database')]
#[IsGranted('ROLE_ADMIN')]
class DatabaseManagerController extends AbstractController
{
    #[Route('', name: 'app_database_index', methods: ['GET'])]
    public function index(DatabaseBackupService $backupService): Response
    {
        return $this->render('database_manager/index.html.twig', [
            'backups' => $backupService->listBackups(),
        ]);
    }

    #[Route('/backup', name: 'app_database_backup', methods: ['POST'])]
    public function backup(Request $request, DatabaseBackupService $backupService, AuditService $auditService): Response
    {
        if ($this->isCsrfTokenValid('backup', $request->request->get('_token'))) {
            try {
                $filename = $backupService->createBackup();
                
                $user = $this->getUser();
                $auditService->persistAndFlushAudit(
                    AuditTipos::SYSTEM_DATABASE_BACKUP,
                    "Respaldo manual generado por administrador. Archivo: $filename",
                    null, null, null, null, null, null, $user
                );

                $this->addFlash('success', "Respaldo '$filename' creado exitosamente.");
            } catch (\Exception $e) {
                $this->addFlash('error', "Error al crear respaldo: " . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF inválido.');
        }
        
        return $this->redirectToRoute('app_database_index');
    }

    #[Route('/import/{filename}', name: 'app_database_import', methods: ['GET', 'POST'])]
    public function import(string $filename, Request $request, DatabaseBackupService $backupService, AuditService $auditService, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Formulario de autorización
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'Su Clave de Administrador',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Debe ingresar su clave para autorizar.')]
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motivo de la restauración',
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'constraints' => [new NotBlank(message: 'Debe justificar esta acción crítica.')]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminUser = $this->getUser();

            if (!$passwordHasher->isPasswordValid($adminUser, $form->get('password')->getData())) {
                $form->get('password')->addError(new FormError('Clave de administrador inválida.'));
            } else {
                try {
                    $backupService->importBackup($filename);

                    $reason = $form->get('reason')->getData();
                    $auditService->persistAndFlushAudit(
                        AuditTipos::SYSTEM_DATABASE_IMPORT,
                        "Restauración de base de datos desde el archivo '$filename'. Motivo: $reason",
                        null, null, null, null, null, null, $adminUser
                    );

                    $this->addFlash('success', 'La base de datos se restauró correctamente.');
                    return $this->redirectToRoute('app_database_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al restaurar: ' . $e->getMessage());
                }
            }
        }

        return $this->render('database_manager/import.html.twig', [
            'filename' => $filename,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{filename}', name: 'app_database_delete', methods: ['POST'])]
    public function delete(string $filename, Request $request, DatabaseBackupService $backupService, AuditService $auditService): Response
    {
        if ($this->isCsrfTokenValid('delete_backup', $request->request->get('_token'))) {
            try {
                $backupService->deleteBackup($filename);

                $user = $this->getUser();
                $auditService->persistAndFlushAudit(
                    AuditTipos::SYSTEM_DATABASE_BACKUP,
                    "Eliminación de archivo de respaldo. Archivo: $filename",
                    null, null, null, null, null, null, $user
                );

                $this->addFlash('success', "Respaldo '$filename' eliminado exitosamente.");
            } catch (\Exception $e) {
                $this->addFlash('error', "Error al eliminar respaldo: " . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF inválido.');
        }

        return $this->redirectToRoute('app_database_index');
    }

    #[Route('/schedule', name: 'app_database_schedule', methods: ['GET', 'POST'])]
    public function schedule(Request $request, \App\Service\BackupSchedulerConfig $configurator): Response
    {
        $config = $configurator->getConfig();

        $form = $this->createFormBuilder($config)
            ->add('enabled', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Habilitar Respaldos Automáticos',
                'required' => false,
            ])
            ->add('frequency', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Frecuencia',
                'choices' => [
                    'Diario' => 'daily',
                    'Semanal' => 'weekly',
                    'Mensual' => 'monthly',
                ],
                'attr' => ['class' => 'form-select noSrchSelect']
            ])
            ->add('day_of_week', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Día de la semana (Para semanal)',
                'choices' => [
                    'Lunes' => 1,
                    'Martes' => 2,
                    'Miércoles' => 3,
                    'Jueves' => 4,
                    'Viernes' => 5,
                    'Sábado' => 6,
                    'Domingo' => 7,
                ],
                'attr' => ['class' => 'form-select noSrchSelect']
            ])
            ->add('day_of_month', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Día del mes (Para mensual)',
                'attr' => ['min' => 1, 'max' => 31, 'class' => 'form-control']
            ])
            ->add('hour', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Hora de ejecución',
                'choices' => array_combine(
                    array_map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23)),
                    range(0, 23)
                ),
                'attr' => ['class' => 'form-select noSrchSelect']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $configurator->saveConfig($form->getData());
            $this->addFlash('success', 'La programación de respaldos ha sido actualizada.');
            return $this->redirectToRoute('app_database_index');
        }

        return $this->render('database_manager/schedule.html.twig', [
            'form' => $form->createView(),
            'last_run' => $config['last_run'] ?? null,
        ]);
    }
}
