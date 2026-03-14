<?php

namespace App\Controller;

use App\Entity\Citas;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/verifications')]
final class PublicVerificationsController extends AbstractController
{
    #[Route('/verificar-cita/{uuid}', name: 'app_public_verifications_verify_cita', methods: ['GET'])]
    public function verifyCita(#[MapEntity(mapping: ['uuid' => 'uuid'])] Citas $cita, EntityManagerInterface $entityManager): Response
    {
        return $this->render('public_verifications/verify_cita.html.twig', [
            'cita' => $cita,
        ]);
    }
}
