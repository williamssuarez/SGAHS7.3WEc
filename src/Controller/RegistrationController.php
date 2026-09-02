<?php

namespace App\Controller;

use App\Entity\StatusRecord;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setUidCreate(-1);
            $user->setCreated(new \DateTime('now'));
            $user->setStatus($entityManager->getRepository(StatusRecord::class)->getActive());
            $user->setRoles(['ROLE_USER', 'ROLE_EXTERNAL']);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user_internal
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@sgahs.com', 'SGAHS Soporte'))
                    ->to((string) $user->getEmail())
                    ->subject('Confirme su correo electrónico - SGAHS')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/check-email', name: 'app_check_inbox')]
    public function checkInbox(): Response
    {
        $user = $this->getUser();

        // GUARD: If they are already verified, kick them out of the holding pen
        if ($user && $user->isVerified()) {
            return $this->redirectToRoute('app_profile_complete');
        }

        return $this->render('registration/check_email.html.twig');
    }

    #[Route('/verify/send-check-email', name: 'app_send_check_inbox')]
    public function sendCheckInbox(): Response
    {
        $user = $this->getUser();

        // GUARD: If they are already verified, kick them out of the holding pen
        if ($user && $user->isVerified()) {
            return $this->redirectToRoute('app_profile_complete');
        }

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('no-reply@sgahs.com', 'SGAHS Soporte'))
                ->to((string) $user->getEmail())
                ->subject('Confirme su correo electrónico - SGAHS')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        return $this->redirectToRoute('app_check_inbox');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // GUARD: If they click an old link but are already verified, just let them in
        if ($user->isVerified()) {
            return $this->redirectToRoute('app_profile_complete');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'El enlace es inválido o ha expirado. Por favor solicite uno nuevo.');
            return $this->redirectToRoute('app_check_inbox');
        }

        $this->addFlash('success', 'Tu correo ha sido verificado exitosamente.');
        // Route them directly to the profile completion form now that they passed the first subscriber check
        return $this->redirectToRoute('app_profile_complete');
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(RateLimiterFactory $resendEmailLimiter): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // GUARD: Prevent guests or verified users from generating new links
        if (!$user || $user->isVerified()) {
            return $this->redirectToRoute('app_profile_complete');
        }

        // 1. Create a limiter uniquely tied to this user's ID
        $limiter = $resendEmailLimiter->create((string) $user->getId());

        // 2. Consume 1 token. If they don't have any tokens left, block the action.
        if (false === $limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Por favor espera 2 minutos antes de solicitar otro enlace.');
            return $this->redirectToRoute('app_check_inbox');
        }

        // 3. Generate and send the email
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('no-reply@sgahs.com', 'SGAHS Soporte'))
                ->to((string) $user->getEmail())
                ->subject('Confirme su correo electrónico - SGAHS')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', 'Se ha enviado un nuevo enlace a tu correo.');
        return $this->redirectToRoute('app_check_inbox');
    }
}
