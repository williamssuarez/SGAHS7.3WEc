<?php

namespace App\Controller;

use App\Entity\PacienteDiscapacidades;
use App\Form\PacienteDiscapacidadesType;
use App\Repository\PacienteDiscapacidadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paciente/discapacidades')]
final class PacienteDiscapacidadesController extends AbstractController
{
    #[Route(name: 'app_paciente_discapacidades_index', methods: ['GET'])]
    public function index(PacienteDiscapacidadesRepository $pacienteDiscapacidadesRepository): Response
    {
        return $this->render('paciente_discapacidades/index.html.twig', [
            'paciente_discapacidades' => $pacienteDiscapacidadesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_paciente_discapacidades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pacienteDiscapacidade = new PacienteDiscapacidades();
        $form = $this->createForm(PacienteDiscapacidadesType::class, $pacienteDiscapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pacienteDiscapacidade);
            $entityManager->flush();

            return $this->redirectToRoute('app_paciente_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_discapacidades/new.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_discapacidades_show', methods: ['GET'])]
    public function show(PacienteDiscapacidades $pacienteDiscapacidade): Response
    {
        return $this->render('paciente_discapacidades/show.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paciente_discapacidades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PacienteDiscapacidades $pacienteDiscapacidade, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PacienteDiscapacidadesType::class, $pacienteDiscapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_paciente_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_discapacidades/edit.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_discapacidades_delete', methods: ['POST'])]
    public function delete(Request $request, PacienteDiscapacidades $pacienteDiscapacidade, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pacienteDiscapacidade->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pacienteDiscapacidade);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_discapacidades_index', [], Response::HTTP_SEE_OTHER);
    }
}
