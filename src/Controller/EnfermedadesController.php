<?php

namespace App\Controller;

use App\Entity\Enfermedades;
use App\Form\EnfermedadesType;
use App\Repository\EnfermedadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enfermedades')]
final class EnfermedadesController extends AbstractController
{
    #[Route(name: 'app_enfermedades_index', methods: ['GET'])]
    public function index(EnfermedadesRepository $enfermedadesRepository): Response
    {
        return $this->render('enfermedades/index.html.twig', [
            'enfermedades' => $enfermedadesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_enfermedades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $enfermedade = new Enfermedades();
        $form = $this->createForm(EnfermedadesType::class, $enfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enfermedade);
            $entityManager->flush();

            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enfermedades/new.html.twig', [
            'enfermedade' => $enfermedade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enfermedades_show', methods: ['GET'])]
    public function show(Enfermedades $enfermedade): Response
    {
        return $this->render('enfermedades/show.html.twig', [
            'enfermedade' => $enfermedade,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_enfermedades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enfermedades $enfermedade, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EnfermedadesType::class, $enfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enfermedades/edit.html.twig', [
            'enfermedade' => $enfermedade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enfermedades_delete', methods: ['POST'])]
    public function delete(Request $request, Enfermedades $enfermedade, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$enfermedade->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($enfermedade);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
    }
}
