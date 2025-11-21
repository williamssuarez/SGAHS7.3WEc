<?php

namespace App\Controller;

use App\Entity\Discapacidades;
use App\Form\DiscapacidadesType;
use App\Repository\DiscapacidadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/discapacidades')]
final class DiscapacidadesController extends AbstractController
{
    #[Route(name: 'app_discapacidades_index', methods: ['GET'])]
    public function index(DiscapacidadesRepository $discapacidadesRepository): Response
    {
        return $this->render('discapacidades/index.html.twig', [
            'discapacidades' => $discapacidadesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_discapacidades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $discapacidade = new Discapacidades();
        $form = $this->createForm(DiscapacidadesType::class, $discapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($discapacidade);
            $entityManager->flush();

            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discapacidades/new.html.twig', [
            'discapacidade' => $discapacidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_discapacidades_show', methods: ['GET'])]
    public function show(Discapacidades $discapacidade): Response
    {
        return $this->render('discapacidades/show.html.twig', [
            'discapacidade' => $discapacidade,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_discapacidades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Discapacidades $discapacidade, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DiscapacidadesType::class, $discapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discapacidades/edit.html.twig', [
            'discapacidade' => $discapacidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_discapacidades_delete', methods: ['POST'])]
    public function delete(Request $request, Discapacidades $discapacidade, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$discapacidade->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($discapacidade);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
    }
}
