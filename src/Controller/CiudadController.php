<?php

namespace App\Controller;

use App\Entity\Ciudad;
use App\Form\CiudadType;
use App\Repository\CiudadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ciudad')]
final class CiudadController extends AbstractController
{
    #[Route(name: 'app_ciudad_index', methods: ['GET'])]
    public function index(CiudadRepository $ciudadRepository): Response
    {
        return $this->render('ciudad/index.html.twig', [
            'ciudads' => $ciudadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ciudad_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ciudad = new Ciudad();
        $form = $this->createForm(CiudadType::class, $ciudad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ciudad);
            $entityManager->flush();

            return $this->redirectToRoute('app_ciudad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ciudad/new.html.twig', [
            'ciudad' => $ciudad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ciudad_show', methods: ['GET'])]
    public function show(Ciudad $ciudad): Response
    {
        return $this->render('ciudad/show.html.twig', [
            'ciudad' => $ciudad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ciudad_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ciudad $ciudad, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CiudadType::class, $ciudad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ciudad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ciudad/edit.html.twig', [
            'ciudad' => $ciudad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ciudad_delete', methods: ['POST'])]
    public function delete(Request $request, Ciudad $ciudad, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ciudad->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ciudad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ciudad_index', [], Response::HTTP_SEE_OTHER);
    }
}
