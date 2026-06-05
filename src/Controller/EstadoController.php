<?php

namespace App\Controller;

use App\Entity\Estado;
use App\Form\EstadoType;
use App\Repository\EstadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/estado')]
final class EstadoController extends AbstractController
{
    #[Route(name: 'app_estado_index', methods: ['GET'])]
    public function index(EstadoRepository $estadoRepository): Response
    {
        return $this->render('estado/index.html.twig', [
            'entities' => $estadoRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_estado_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $estado = new Estado();
        $form = $this->createForm(EstadoType::class, $estado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($estado);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_estado_show', ['id' => $estado->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('estado/new.html.twig', [
            'estado' => $estado,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_estado_show', methods: ['GET'])]
    public function show(Estado $estado): Response
    {
        return $this->render('estado/show.html.twig', [
            'entity' => $estado,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_estado_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Estado $estado, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EstadoType::class, $estado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_estado_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('estado/edit.html.twig', [
            'estado' => $estado,
            'form' => $form,
        ]);
    }
}
