<?php

namespace App\Controller;

use App\Entity\Citas;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/verifications')]
final class PublicVerificationsController extends AbstractController
{

    #[Route('/verificar-cita/{uuid}', name: 'app_public_verifications_verify_cita', methods: ['GET'])]
    public function verifyCita($uuid, EntityManagerInterface $entityManager): Response
    {
        $cita = $entityManager->getRepository(Citas::class)->findOneBy(['uuid' => $uuid]);
        return $this->render('public_verifications/verify_cita.html.twig', [
            'cita' => $cita,
        ]);
    }
}
