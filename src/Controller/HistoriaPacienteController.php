<?php

namespace App\Controller;

use App\Entity\HistoriaPaciente;
use App\Entity\Paciente;
use App\Form\HistoriaPacienteType;
use App\Repository\HistoriaPacienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/historia/paciente')]
final class HistoriaPacienteController extends AbstractController
{
    #[Route(name: 'app_historia_paciente_index', methods: ['GET'])]
    public function index(HistoriaPacienteRepository $historiaPacienteRepository): Response
    {
        return $this->render('historia_paciente/index.html.twig', [
            'historia_pacientes' => $historiaPacienteRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new', name: 'app_historia_paciente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Paciente $paciente, EntityManagerInterface $entityManager): Response
    {
        $historiaPaciente = new HistoriaPaciente();
        $form = $this->createForm(HistoriaPacienteType::class, $historiaPaciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $historiaPaciente->setDoctor($this->getUser());
            $historiaPaciente->setPaciente($paciente);
            $historiaPaciente->setFechaAtendido(new \DateTime('now'));

            $entityManager->persist($historiaPaciente);
            $entityManager->flush();

            return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('historia_paciente/new.html.twig', [
            'historia_paciente' => $historiaPaciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historia_paciente_show', methods: ['GET'])]
    public function show(HistoriaPaciente $historiaPaciente): Response
    {
        return $this->render('historia_paciente/show.html.twig', [
            'historia_paciente' => $historiaPaciente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_historia_paciente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HistoriaPaciente $historiaPaciente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HistoriaPacienteType::class, $historiaPaciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_historia_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('historia_paciente/edit.html.twig', [
            'historia_paciente' => $historiaPaciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_historia_paciente_delete', methods: ['POST'])]
    public function delete(Request $request, HistoriaPaciente $historiaPaciente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$historiaPaciente->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($historiaPaciente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_historia_paciente_index', [], Response::HTTP_SEE_OTHER);
    }
}
