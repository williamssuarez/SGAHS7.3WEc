<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\PacienteEnfermedades;
use App\Form\PacienteEnfermedadesType;
use App\Repository\PacienteEnfermedadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paciente/enfermedades')]
final class PacienteEnfermedadesController extends AbstractController
{
    #[Route(name: 'app_paciente_enfermedades_index', methods: ['GET'])]
    public function index(PacienteEnfermedadesRepository $pacienteEnfermedadesRepository): Response
    {
        return $this->render('paciente_enfermedades/index.html.twig', [
            'paciente_enfermedades' => $pacienteEnfermedadesRepository->findAll(),
        ]);
    }

    #[Route('{id}/new-consulta', name: 'app_paciente_enfermedades_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta): Response
    {
        $pacienteEnfermedade = new PacienteEnfermedades();
        $form = $this->createForm(PacienteEnfermedadesType::class, $pacienteEnfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pacienteEnfermedade->setPaciente($consulta->getPaciente());
            $entityManager->persist($pacienteEnfermedade);
            $entityManager->flush();

            $this->addFlash('success', 'Enfermedad Agregada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_enfermedades/newConsulta.html.twig', [
            'paciente_enfermedade' => $pacienteEnfermedade,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_enfermedades_show', methods: ['GET'])]
    public function show(PacienteEnfermedades $pacienteEnfermedade): Response
    {
        return $this->render('paciente_enfermedades/show.html.twig', [
            'paciente_enfermedade' => $pacienteEnfermedade,
        ]);
    }

    #[Route('/{id}/edit-consulta', name: 'app_paciente_enfermedades_edit_consulta', methods: ['GET', 'POST'])]
    public function editConsulta(Request $request, PacienteEnfermedades $pacienteEnfermedade, EntityManagerInterface $entityManager, Consulta $consulta): Response
    {
        $form = $this->createForm(PacienteEnfermedadesType::class, $pacienteEnfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Enfermedad Editada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_enfermedades/editConsulta.html.twig', [
            'paciente_enfermedade' => $pacienteEnfermedade,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_enfermedades_delete', methods: ['POST'])]
    public function delete(Request $request, PacienteEnfermedades $pacienteEnfermedade, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pacienteEnfermedade->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pacienteEnfermedade);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_enfermedades_index', [], Response::HTTP_SEE_OTHER);
    }
}
