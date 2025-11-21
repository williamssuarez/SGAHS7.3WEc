<?php

namespace App\Controller;

use App\Entity\Tratamientos;
use App\Form\TratamientosType;
use App\Repository\TratamientosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tratamientos')]
final class TratamientosController extends AbstractController
{
    #[Route(name: 'app_tratamientos_index', methods: ['GET'])]
    public function index(TratamientosRepository $tratamientosRepository): Response
    {
        return $this->render('tratamientos/index.html.twig', [
            'tratamientos' => $tratamientosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tratamientos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tratamiento = new Tratamientos();
        $form = $this->createForm(TratamientosType::class, $tratamiento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tratamiento);
            $entityManager->flush();

            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tratamientos/new.html.twig', [
            'tratamiento' => $tratamiento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tratamientos_show', methods: ['GET'])]
    public function show(Tratamientos $tratamiento): Response
    {
        return $this->render('tratamientos/show.html.twig', [
            'tratamiento' => $tratamiento,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tratamientos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tratamientos $tratamiento, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TratamientosType::class, $tratamiento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tratamientos/edit.html.twig', [
            'tratamiento' => $tratamiento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tratamientos_delete', methods: ['POST'])]
    public function delete(Request $request, Tratamientos $tratamiento, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tratamiento->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tratamiento);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
    }
}
