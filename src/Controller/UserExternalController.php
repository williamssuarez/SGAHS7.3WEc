<?php

namespace App\Controller;

use App\Entity\Audit;
use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Enum\AuditTipos;
use App\Form\UserAdminAuditReasonsType;
use App\Form\UserExternalAdminType;
use App\Form\UserType;
use App\Repository\StatusRecordRepository;
use App\Repository\UserRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/user_external')]
final class UserExternalController extends AbstractController
{
    #[Route(name: 'app_user_external_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('users/user_external/index.html.twig', [
            'users' => $userRepository->getAllExternalsforTable(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_external_show', methods: ['GET'])]
    public function show(User $user, StatusRecordRepository $recordRepository): Response
    {
        if ($user->getStatus() == $recordRepository->getRemove()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/user_external/show.html.twig', [
            'user' => $user,
            'profile' => $user->getExternalProfile()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_external_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository, UserPasswordHasherInterface $passwordHasher, AuditService $auditService): Response
    {
        if ($user->getStatus() != $recordRepository->getActive()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserExternalAdminType::class, $user->getExternalProfile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 2. Get the currently authenticated administrator
            $adminUser = $this->getUser();

            // 3. Extract the unmapped password from the form
            $plainPassword = $form->get('password')->getData();
            $plainReason = $form->get('reason')->getData();

            // 4. Validate the password
            if (!$passwordHasher->isPasswordValid($adminUser, $plainPassword)) {
                // Attaches the error directly to the password input field in Twig
                $form->get('password')->addError(new FormError('Clave inválida.'));
            } else {

                // 5. Success block
                // $reason = $form->get('reason')->getData();
                // TODO: Save $reason and $adminUser->getId() to your AuditLog entity here
                $cedula = $user->getExternalProfile()->getNroDocumento();
                $auditService->persistAudit(
                    AuditTipos::EXTERNAL_USER_ADMIN_EDIT,
                    "Se modificaron los datos del usuario con cedula $cedula. Por motivo: $plainReason",
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $user,
                    null,
                    $user->getExternalProfile(),
                );

                $auditService->persistEditionAndFlushAudit(
                    $user->getExternalProfile(),
                    AuditTipos::EXTERNAL_USER_ADMIN_EDIT,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $user->getExternalProfile(),
                );

                $this->addFlash('success', 'El perfil externo ha sido actualizado exitosamente.');
                return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('users/user_external/edit.html.twig', [
            'user' => $user,
            'profile' => $user->getExternalProfile(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/block', name: 'app_user_external_block', methods: ['GET', 'POST'])]
    public function block(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository, UserPasswordHasherInterface $passwordHasher, AuditService $auditService): Response
    {
        if ($user->getStatus() != $recordRepository->getActive()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserAdminAuditReasonsType::class, $user->getExternalProfile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 2. Get the currently authenticated administrator
            $adminUser = $this->getUser();

            // 3. Extract the unmapped password from the form
            $plainPassword = $form->get('password')->getData();
            $plainReason = $form->get('reason')->getData();

            // 4. Validate the password
            if (!$passwordHasher->isPasswordValid($adminUser, $plainPassword)) {
                // Attaches the error directly to the password input field in Twig
                $form->get('password')->addError(new FormError('Clave inválida.'));
            } else {

                // 5. Success block
                $user->setStatus($recordRepository->getLockedUser());
                $entityManager->persist($user);

                //Auditar
                $cedula = $user->getExternalProfile()->getNroDocumento();
                $correo = $user->getEmail();
                $auditService->persistAndFlushAudit(
                    AuditTipos::EXTERNAL_USER_ADMIN_BLOCK,
                    "Se bloqueo el acceso a la cuenta del usuario con cedula $cedula y correo $correo . Por motivo: $plainReason",
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $user,
                );

                $this->addFlash('success', 'Se ha bloqueado al usuario exitosamente.');
                return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('users/user_external/block.html.twig', [
            'user' => $user,
            'profile' => $user->getExternalProfile(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/unblock', name: 'app_user_external_unblock', methods: ['GET', 'POST'])]
    public function unblock(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository, UserPasswordHasherInterface $passwordHasher, AuditService $auditService): Response
    {
        if ($user->getStatus() != $recordRepository->getLockedUser()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserAdminAuditReasonsType::class, $user->getExternalProfile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 2. Get the currently authenticated administrator
            $adminUser = $this->getUser();

            // 3. Extract the unmapped password from the form
            $plainPassword = $form->get('password')->getData();
            $plainReason = $form->get('reason')->getData();

            // 4. Validate the password
            if (!$passwordHasher->isPasswordValid($adminUser, $plainPassword)) {
                // Attaches the error directly to the password input field in Twig
                $form->get('password')->addError(new FormError('Clave inválida.'));
            } else {

                // 5. Success block
                $user->setStatus($recordRepository->getActive());
                $entityManager->persist($user);

                //Auditar
                $cedula = $user->getExternalProfile()->getNroDocumento();
                $correo = $user->getEmail();
                $auditService->persistAndFlushAudit(
                    AuditTipos::EXTERNAL_USER_ADMIN_UNBLOCK,
                    "Se desbloqueo el acceso a la cuenta del usuario con cedula $cedula y correo $correo . Por motivo: $plainReason",
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $user,
                );

                $this->addFlash('success', 'Se ha desbloqueado al usuario exitosamente.');
                return $this->redirectToRoute('app_user_external_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('users/user_external/unblock.html.twig', [
            'user' => $user,
            'profile' => $user->getExternalProfile(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/forzar-reinicio', name: 'app_user_external_force_reset', methods: ['GET', 'POST'])]
    public function forceReset(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        AuditService $auditService
    ): Response {

        // 1. Build the Admin Authorization Form on the fly
        $form = $this->createFormBuilder()
            ->add('password', PasswordType::class, [
                'label' => 'Su Clave de Administrador',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Debe ingresar su clave para autorizar.')]
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motivo del Reinicio',
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'constraints' => [new NotBlank(message: 'Debe justificar esta acción.')]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adminUser = $this->getUser();

            // 2. Verify Admin Password
            if (!$passwordHasher->isPasswordValid($adminUser, $form->get('password')->getData())) {
                $form->get('password')->addError(new FormError('Clave de administrador inválida.'));
            } else {

                // 3. Generate the Token for the TARGET user
                try {
                    $resetToken = $resetPasswordHelper->generateResetToken($user);
                } catch (ResetPasswordExceptionInterface $e) {
                    // This triggers if an admin already clicked this button recently (cooldown)
                    $this->addFlash('error', sprintf('No se pudo generar el enlace: %s', $e->getReason()));
                    return $this->redirectToRoute('app_user_external_index');
                }

                // 4. Build and Send the Email
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@sgahs.com', 'SGAHS Soporte'))
                    ->to($user->getEmail())
                    ->subject('Su contraseña ha sido reiniciada por un administrador')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'resetToken' => $resetToken,
                        'reason' => $form->get('reason')->getData()
                    ]);

                $mailer->send($email);

                $user->setStatus($entityManager->getRepository(StatusRecord::class)->getExpiredCredentialUser());

                //Auditar
                $cedula = $user->getExternalProfile()->getNroDocumento();
                $correo = $user->getEmail();
                $plainReason = $form->get('reason')->getData();

                $auditService->persistAndFlushAudit(
                    AuditTipos::EXTERNAL_USER_ADMIN_UNBLOCK,
                    "Se reiniciaron las credenciales del usuario con cedula $cedula y correo $correo . Por motivo: $plainReason",
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $user,
                );

                $this->addFlash('success', 'Se ha enviado el enlace de recuperación al correo del usuario.');
                return $this->redirectToRoute('app_user_external_index');
            }
        }

        return $this->render('users/user_external/admin_reset.html.twig', [
            'target_user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_external_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $submittedToken)) {
            $user->setStatus($recordRepository->getRemove());
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
        //return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
    }
}
