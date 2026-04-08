<?php

namespace App\Controller;

use App\Entity\Quirofano;
use App\Form\QuirofanoType;
use App\Repository\QuirofanoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quirofano')]
final class QuirofanoController extends AbstractController
{
    #[Route(name: 'app_quirofano_index', methods: ['GET'])]
    public function index(QuirofanoRepository $quirofanoRepository): Response
    {
        return $this->render('quirofano/index.html.twig', [
            'entities' => $quirofanoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_quirofano_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quirofano = new Quirofano();
        $form = $this->createForm(QuirofanoType::class, $quirofano);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quirofano->setEstado('available');
            $entityManager->persist($quirofano);
            $entityManager->flush();

            return $this->redirectToRoute('app_quirofano_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quirofano/new.html.twig', [
            'entity' => $quirofano,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quirofano_show', methods: ['GET'])]
    public function show(Quirofano $quirofano): Response
    {
        return $this->render('quirofano/show.html.twig', [
            'quirofano' => $quirofano,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quirofano_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quirofano $quirofano, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuirofanoType::class, $quirofano);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quirofano_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quirofano/edit.html.twig', [
            'entity' => $quirofano,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quirofano_delete', methods: ['POST'])]
    public function delete(Request $request, Quirofano $quirofano, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quirofano->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($quirofano);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quirofano_index', [], Response::HTTP_SEE_OTHER);
    }
}
