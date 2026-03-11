<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\PacienteInmunizaciones;
use App\Enum\AuditTipos;
use App\Form\PacienteInmunizacionesType;
use App\Repository\PacienteInmunizacionesRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paciente/inmunizaciones')]
final class PacienteInmunizacionesController extends AbstractController
{
    #[Route(name: 'app_paciente_inmunizaciones_index', methods: ['GET'])]
    public function index(PacienteInmunizacionesRepository $pacienteInmunizacionesRepository): Response
    {
        return $this->render('paciente_inmunizaciones/index.html.twig', [
            'paciente_inmunizaciones' => $pacienteInmunizacionesRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new-consulta', name: 'app_paciente_inmunizaciones_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $pacienteInmunizacione = new PacienteInmunizaciones();
        $form = $this->createForm(PacienteInmunizacionesType::class, $pacienteInmunizacione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pacienteInmunizacione->setPaciente($consulta->getPaciente());
            $entityManager->persist($pacienteInmunizacione);

            $name = $consulta->getPaciente()->getNombre();
            $immunizationName = $pacienteInmunizacione->getInmunizacion()->fullName();
            $dosis = $pacienteInmunizacione->getDosis()->getReadableText();

            $auditService->persistAudit(
                AuditTipos::CONSULT_IMMUNIZATION_NEW,
                "Nueva Inmunizacion: $immunizationName ($dosis) para $name",
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Inmunizacion Agregada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_inmunizaciones/newConsulta.html.twig', [
            'paciente_inmunizacione' => $pacienteInmunizacione,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_inmunizaciones_show', methods: ['GET'])]
    public function show(PacienteInmunizaciones $pacienteInmunizacione): Response
    {
        return $this->render('paciente_inmunizaciones/show.html.twig', [
            'paciente_inmunizacione' => $pacienteInmunizacione,
        ]);
    }

    #[Route('/{id}/edit/{consulta}', name: 'app_paciente_inmunizaciones_edit_consulta', methods: ['GET', 'POST'])]
    public function editConsulta(Request $request, PacienteInmunizaciones $pacienteInmunizacione, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $form = $this->createForm(PacienteInmunizacionesType::class, $pacienteInmunizacione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auditService->persistEditionAudit(
                $pacienteInmunizacione,
                AuditTipos::CONSULT_IMMUNIZATION_EDIT,
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Inmunizacion Agregada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_inmunizaciones/editConsulta.html.twig', [
            'paciente_inmunizacione' => $pacienteInmunizacione,
            'form' => $form,
            'consultum' => $consulta
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_inmunizaciones_delete', methods: ['POST'])]
    public function delete(Request $request, PacienteInmunizaciones $pacienteInmunizacione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pacienteInmunizacione->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pacienteInmunizacione);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_inmunizaciones_index', [], Response::HTTP_SEE_OTHER);
    }
}
