<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\PacienteDiscapacidades;
use App\Enum\AuditTipos;
use App\Form\PacienteDiscapacidadesType;
use App\Repository\PacienteDiscapacidadesRepository;
use App\Service\AuditService;
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

    #[Route('/{id}/new-consulta', name: 'app_paciente_discapacidades_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $pacienteDiscapacidade = new PacienteDiscapacidades();
        $form = $this->createForm(PacienteDiscapacidadesType::class, $pacienteDiscapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pacienteDiscapacidade->setPaciente($consulta->getPaciente());
            $entityManager->persist($pacienteDiscapacidade);

            $name = $consulta->getPaciente()->getNombre();
            $disabilityName = $pacienteDiscapacidade->getDiscapacidad()->fullName();
            $porcentaje = $pacienteDiscapacidade->getPorcentaje(). '%';

            $auditService->persistAudit(
                AuditTipos::CONSULT_DISABILITY_NEW,
                "Nueva discapacidad: $porcentaje de $disabilityName para $name",
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Discapacidad Agregada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_discapacidades/newConsulta.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
            'form' => $form,
            'consultum' => $consulta
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_discapacidades_show', methods: ['GET'])]
    public function show(PacienteDiscapacidades $pacienteDiscapacidade): Response
    {
        return $this->render('paciente_discapacidades/show.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
        ]);
    }

    #[Route('/{id}/edit/{consulta}', name: 'app_paciente_discapacidades_edit_consulta', methods: ['GET', 'POST'])]
    public function editConsulta(Request $request, PacienteDiscapacidades $pacienteDiscapacidade, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $form = $this->createForm(PacienteDiscapacidadesType::class, $pacienteDiscapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auditService->persistEditionAudit(
                $pacienteDiscapacidade,
                AuditTipos::CONSULT_DISABILITY_EDIT,
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Discapacidad Editada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_discapacidades/editConsulta.html.twig', [
            'paciente_discapacidade' => $pacienteDiscapacidade,
            'form' => $form,
            'consultum' => $consulta
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
