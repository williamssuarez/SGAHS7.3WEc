<?php

namespace App\Controller;

use App\Entity\ExternalProfile;
use App\Entity\User;
use App\Form\ExternalProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/profile/complete', name: 'app_profile_complete')]
    public function complete(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // 1. Security: If user is not logged in or already has a profile, send them away
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getExternalProfile() && $user->getExternalProfile()->getNroDocumento()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // 2. Handle the Form
        $profile = new ExternalProfile();
        $form = $this->createForm(ExternalProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Link the profile to the user
            $profile->setWebUser($user); // Ensure this setter exists in ExternalProfile
            $user->setExternalProfile($profile);

            $entityManager->persist($profile);
            $entityManager->flush();

            $this->addFlash('success', '¡Perfil completado con éxito!');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('profile/complete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
