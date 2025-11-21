<?php

namespace App\Controller;

use App\Entity\Medicamentos;
use App\Form\MedicamentosType;
use App\Repository\MedicamentosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/medicamentos')]
final class MedicamentosController extends AbstractController
{
    #[Route(name: 'app_medicamentos_index', methods: ['GET'])]
    public function index(MedicamentosRepository $medicamentosRepository): Response
    {
        return $this->render('medicamentos/index.html.twig', [
            'medicamentos' => $medicamentosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_medicamentos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medicamento = new Medicamentos();
        $form = $this->createForm(MedicamentosType::class, $medicamento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medicamento);
            $entityManager->flush();

            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicamentos/new.html.twig', [
            'medicamento' => $medicamento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicamentos_show', methods: ['GET'])]
    public function show(Medicamentos $medicamento): Response
    {
        return $this->render('medicamentos/show.html.twig', [
            'medicamento' => $medicamento,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicamentos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medicamentos $medicamento, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedicamentosType::class, $medicamento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicamentos/edit.html.twig', [
            'medicamento' => $medicamento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicamentos_delete', methods: ['POST'])]
    public function delete(Request $request, Medicamentos $medicamento, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medicamento->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($medicamento);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
    }
}
