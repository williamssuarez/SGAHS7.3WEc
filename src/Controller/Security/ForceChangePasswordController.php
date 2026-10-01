<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Enum\AuditTipos;
use App\Form\ChangePasswordFormType;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ForceChangePasswordController extends AbstractController
{
    #[Route('/forzar-cambio-clave', name: 'app_change_password')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isMustChangePassword()) {
            return $this->redirectToRoute('app_paciente_index');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password, and set it.
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $user->setMustChangePassword(false);

            $auditService->persistAudit(
                tipo: AuditTipos::USER_PASSWORD_CHANGED_MANDATORY,
                mensaje: 'El usuario ha cambiado su contraseña obligatoriamente por motivos de seguridad.',
                usuario: $user
            );

            $entityManager->flush();

            // The session is cleaned up after the password has been changed.
            // This forces the user to log in again. Or we can just redirect to dashboard.
            $this->addFlash('success', 'Su contraseña ha sido cambiada exitosamente.');
            return $this->redirectToRoute('app_paciente_index');
        }

        return $this->render('security/force_change_password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
