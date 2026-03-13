<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\Audit;
use App\Entity\Citas;
use App\Entity\Consulta;
use App\Entity\PacienteCondiciones;
use App\Entity\PacienteDiscapacidades;
use App\Entity\PacienteEnfermedades;
use App\Entity\PacienteInmunizaciones;
use App\Entity\Prescripciones;
use App\Entity\StatusRecord;
use App\Entity\Vitales;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use App\Enum\ConsultaEstados;
use App\Enum\PrescripcionesEstados;
use App\Exception\BusinessRuleException;
use App\Form\ConsultaActiveType;
use App\Form\ConsultaCancelType;
use App\Form\ConsultaPendingType;
use App\Repository\ConsultaRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consulta')]
final class ConsultaController extends AbstractController
{
    #[Route('/pending', name: 'app_consulta_pendientes_index', methods: ['GET'])]
    public function indexPendientes(ConsultaRepository $consultaRepository): Response
    {
        return $this->render('consulta/index.html.twig', [
            'entities' => $consultaRepository->getActivesforTableByState(ConsultaEstados::PENDING),
        ]);
    }

    #[Route('/active', name: 'app_consulta_activas_index', methods: ['GET'])]
    public function indexActivas(ConsultaRepository $consultaRepository): Response
    {
        return $this->render('consulta/index.html.twig', [
            'entities' => $consultaRepository->getActivesforTableByState(ConsultaEstados::ACTIVE),
        ]);
    }

    #[Route(name: 'app_consulta_index', methods: ['GET'])]
    public function index(ConsultaRepository $consultaRepository): Response
    {
        return $this->render('consulta/index.html.twig', [
            'entities' => $consultaRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new-pending', name: 'app_consulta_new_pending', methods: ['GET', 'POST'])]
    public function newPending(Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $consultum = new Consulta();
        $form = $this->createForm(ConsultaPendingType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $consultum->setEstadoConsulta(ConsultaEstados::PENDING);
            $entityManager->persist($consultum);

            $auditService->persistAudit(
                AuditTipos::CONSULT_PENDING,
                "Consulta programada exitosamente.",
                $consultum->getPaciente(),
                $consultum
            );

            $entityManager->flush();

            $this->addFlash('success', 'Consulta Programada.');
            return $this->redirectToRoute('app_consulta_pendientes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/new.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
            'consultaLabel' => 'Nueva Consulta Pendiente'
        ]);
    }

    #[Route('/new-active', name: 'app_consulta_new_active', methods: ['GET', 'POST'])]
    public function newActive(Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $consultum = new Consulta();
        $form = $this->createForm(ConsultaActiveType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $checkConsulta = $entityManager->getRepository(Consulta::class)->findOneBy([
                'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
                'estadoConsulta' => ConsultaEstados::ACTIVE,
                'paciente' => $consultum->getPaciente(),
            ]);
            if ($checkConsulta) {
                $this->addFlash('error', 'Este paciente ya tiene una consulta en progreso.');
                return $this->redirectToRoute('app_consulta_activas_index', [], Response::HTTP_SEE_OTHER);
            }

            $consultum->setEstadoConsulta(ConsultaEstados::ACTIVE);
            $entityManager->persist($consultum);

            $auditService->persistAudit(
                AuditTipos::CONSULT_ACTIVE,
                "Consulta iniciada exitosamente.",
                $consultum->getPaciente(),
                $consultum
            );
            $entityManager->flush();
            $this->addFlash('success', 'Consulta Creada e Iniciada.');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consultum->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/new.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
            'consultaLabel' => 'Nueva Consulta Activa'
        ]);
    }

    #[Route('/{id}', name: 'app_consulta_show', methods: ['GET'])]
    public function show(Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        $paciente = $consultum->getPaciente();

        //vitales
        $vitales = $entityManager->getRepository(Vitales::class)->getActivesforTable($paciente->getId());
        $currentVitals = $entityManager->getRepository(Vitales::class)->getCurrentActiveVitalsforTable($paciente->getId());

        //prescripciones
        $prescripcionesActivas = $entityManager->getRepository(Prescripciones::class)->getActivesforTableByState($paciente->getId(), PrescripcionesEstados::ACTIVE);
        $prescripcionesInactivas = $entityManager->getRepository(Prescripciones::class)->getActivesforTableByNotState($paciente->getId(), PrescripcionesEstados::ACTIVE);

        //alergias
        $alergias = $entityManager->getRepository(Alergias::class)->getActivesforTable($paciente->getId());

        //condiciones
        $condiciones = $entityManager->getRepository(PacienteCondiciones::class)->getActivesforTable($paciente->getId());

        //enfermedades
        $enfermedades = $entityManager->getRepository(PacienteEnfermedades::class)->getActivesforTable($paciente->getId());

        //discapacidades
        $discapacidades = $entityManager->getRepository(PacienteDiscapacidades::class)->getActivesforTable($paciente->getId());

        //inmunizaciones
        $inmunizaciones = $entityManager->getRepository(PacienteInmunizaciones::class)->getActivesforTable($paciente->getId());

        //historial de la consulta
        $consultHistory = $entityManager->getRepository(Audit::class)->findBy([
            'paciente' => $paciente->getId(),
            'consulta' => $consultum->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ], ['id' => 'DESC']);

        //historial completo
        $allHistory = $entityManager->getRepository(Audit::class)->findBy([
            'paciente' => $paciente->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ], ['id' => 'DESC']);

        return $this->render('consulta/show.html.twig', [
            'consultum' => $consultum,
            'paciente' => $consultum->getPaciente(),
            'vitales' => $vitales,
            'currentVitals' => $currentVitals,
            'prescripcionesActivas' => $prescripcionesActivas,
            'prescripcionesInactivas' => $prescripcionesInactivas,
            'alergias' => $alergias,
            'condiciones' => $condiciones,
            'enfermedades' => $enfermedades,
            'discapacidades' => $discapacidades,
            'inmunizaciones' => $inmunizaciones,
            'consultHistory' => $consultHistory,
            'allHistory' => $allHistory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consulta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConsultaPendingType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/edit.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/iniciar', name: 'app_consulta_iniciar', methods: ['POST'])]
    public function iniciar(Consulta $consulta, EntityManagerInterface $em, AuditService $auditService): Response
    {
        $checkConsulta = $em->getRepository(Consulta::class)->findOneBy([
            'status' => $em->getRepository(StatusRecord::class)->getActive(),
            'estadoConsulta' => ConsultaEstados::ACTIVE,
            'paciente' => $consulta->getPaciente(),
        ]);
        if ($checkConsulta) {
            $this->addFlash('error', 'Este paciente ya tiene una consulta en progreso.');
            return $this->redirectToRoute('app_consulta_activas_index', [], Response::HTTP_SEE_OTHER);
        }

        $consulta->setEstadoConsulta(ConsultaEstados::ACTIVE);
        $consulta->setFechaInicio(new \DateTime('now'));
        $em->persist($consulta);

        $auditService->persistAudit(
            AuditTipos::CONSULT_ACTIVE,
            "Consulta iniciada exitosamente.",
            $consulta->getPaciente(),
            $consulta
        );

        $em->flush();

        $this->addFlash('success', 'La consulta ha sido iniciada.');
        return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()]);
    }

    #[Route('/{id}/finalizar', name: 'app_consulta_finalizar', methods: ['POST'])]
    public function finalizar(Consulta $consulta, EntityManagerInterface $em, AuditService $auditService): Response
    {
        $consulta->setEstadoConsulta(ConsultaEstados::FINISHED);
        $consulta->setFechaFin(new \DateTime('now'));
        $em->persist($consulta);

        $cita = $em->getRepository(Citas::class)->findOneBy([
            'consulta' => $consulta->getId(),
            'status' => $em->getRepository(StatusRecord::class)->getActive()
        ]);

        if ($cita) {
            $cita->setEstadoCita(CitasEstados::COMPLETED);
            $em->persist($cita);
        }

        // 2. Audit the event
        $auditService->persistAudit(
            AuditTipos::CONSULT_FINISHED,
            "Consulta finalizada exitosamente.",
            $consulta->getPaciente(),
            $consulta
        );

        $em->flush();

        $this->addFlash('success', 'La consulta ha sido cerrada y guardada en el historial.');
        return $this->redirectToRoute('app_paciente_show', ['id' => $consulta->getPaciente()->getId()]);
    }

    #[Route('/{id}/cancel', name: 'app_consulta_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, Consulta $consultum, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $form = $this->createForm(ConsultaCancelType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consultum->setEstadoConsulta(ConsultaEstados::CANCELED);
            $entityManager->persist($consultum);

            $razon = $consultum->getObservacion();
            $auditService->persistAudit(
                AuditTipos::CONSULT_CANCELED,
                "Consulta Cancelada: $razon.",
                $consultum->getPaciente(),
                $consultum
            );

            $entityManager->flush();

            $this->addFlash('success', 'Consulta Cancelada.');
            return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/cancel.html.twig', [
            'entity' => $consultum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consulta_delete', methods: ['POST'])]
    public function delete(Request $request, Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$consultum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($consultum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
    }
}
