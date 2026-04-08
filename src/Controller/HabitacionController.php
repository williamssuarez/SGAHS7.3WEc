<?php

namespace App\Controller;

use App\Entity\Habitacion;
use App\Form\HabitacionType;
use App\Repository\HabitacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/habitacion')]
final class HabitacionController extends AbstractController
{
    #[Route(name: 'app_habitacion_index', methods: ['GET'])]
    public function index(HabitacionRepository $habitacionRepository): Response
    {
        return $this->render('habitacion/index.html.twig', [
            'entities' => $habitacionRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_habitacion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $habitacion = new Habitacion();
        $form = $this->createForm(HabitacionType::class, $habitacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($habitacion);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_habitacion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('habitacion/new.html.twig', [
            'habitacion' => $habitacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_habitacion_show', methods: ['GET'])]
    public function show(Habitacion $habitacion): Response
    {
        return $this->render('habitacion/show.html.twig', [
            'entity' => $habitacion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_habitacion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Habitacion $habitacion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HabitacionType::class, $habitacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_habitacion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('habitacion/edit.html.twig', [
            'habitacion' => $habitacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_habitacion_delete', methods: ['POST'])]
    public function delete(Request $request, Habitacion $habitacion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$habitacion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($habitacion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_habitacion_index', [], Response::HTTP_SEE_OTHER);
    }
}
