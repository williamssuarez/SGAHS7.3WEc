<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mi-perfil')]
#[IsGranted('ROLE_INTERNAL')]
class MyInternalProfileController extends AbstractController
{
    #[Route('', name: 'my_internal_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        $user = $this->getUser();

        if (!$user || !$user->getInternalProfile()) {
            $this->addFlash('error', 'No se encontró el perfil interno asociado a su cuenta.');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('users/user_internal/my_profile.html.twig', [
            'user' => $user,
            'profile' => $user->getInternalProfile()
        ]);
    }
}
