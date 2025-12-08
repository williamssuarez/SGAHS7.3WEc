<?php

namespace App\Controller;

use App\Entity\Paciente;
use App\Form\PacienteType;
use App\Repository\HistoriaPacienteRepository;
use App\Repository\PacienteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paciente')]
final class PacienteController extends AbstractController
{
    #[Route(name: 'app_paciente_index', methods: ['GET'])]
    public function index(PacienteRepository $pacienteRepository): Response
    {
        return $this->render('paciente/index.html.twig', [
            'pacientes' => $pacienteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_paciente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paciente = new Paciente();
        $form = $this->createForm(PacienteType::class, $paciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($paciente);
            $entityManager->flush();

            return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente/new.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_show', methods: ['GET'])]
    public function show(Paciente $paciente, HistoriaPacienteRepository $historiaPacienteRepository): Response
    {
        $historias = $historiaPacienteRepository->findByPacienteOrderedByDate($paciente->getId());

        $groupedHistorias = [];
        foreach ($historias as $historia) {
            // Get the date and format it as a string for grouping (e.g., "10 Feb. 2023")
            // Use IntlDateFormatter or a simple format string, like below:
            $dateString = $historia->getFechaAtendido()->format('j M. Y');

            // Initialize the array for this date if it doesn't exist
            if (!isset($groupedHistorias[$dateString])) {
                $groupedHistorias[$dateString] = [];
            }

            // Add the history record to the group
            $groupedHistorias[$dateString][] = $historia;
        }

        return $this->render('paciente/show.html.twig', [
            'paciente' => $paciente,
            'grouped_historias' => $groupedHistorias
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paciente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Paciente $paciente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PacienteType::class, $paciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente/edit.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_delete', methods: ['POST'])]
    public function delete(Request $request, Paciente $paciente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$paciente->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($paciente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
    }
}
