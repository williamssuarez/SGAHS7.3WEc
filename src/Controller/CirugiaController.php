<?php

namespace App\Controller;

use App\Entity\Cirugia;
use App\Entity\Citas;
use App\Entity\Hospitalizaciones;
use App\Entity\Quirofano;
use App\Enum\AuditTipos;
use App\Enum\CirugiaEstados;
use App\Enum\CitasEstados;
use App\Form\CirugiaType;
use App\Repository\CirugiaRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cirugia')]
final class CirugiaController extends AbstractController
{
    #[Route('/', name: 'app_cirugia')]
    public function index(): Response
    {
        return $this->render('cirugia/index.html.twig', [
            'controller_name' => 'CirugiaController',
        ]);
    }

    #[Route('/programar/{hospitalizacion_id?}', name: 'app_cirugia_programar', methods: ['GET', 'POST'])]
    public function programar(Request $request, EntityManagerInterface $em, ?int $hospitalizacion_id): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN_QUIROFANO'); // Or whoever manages the OR schedule

        $cirugia = new Cirugia();

        // SPEED HACK: Auto-fill if coming from a hospital bed!
        if ($hospitalizacion_id) {
            $hospitalizacion = $em->getRepository(Hospitalizaciones::class)->find($hospitalizacion_id);
            if ($hospitalizacion) {
                $cirugia->setHospitalizacionOrigen($hospitalizacion);
                $cirugia->setPaciente($hospitalizacion->getPaciente());
                // You could even pre-fill the diagnostico if you wanted!
            }
        }

        $form = $this->createForm(CirugiaType::class, $cirugia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Lock in the starting state
            $cirugia->setEstado(CirugiaEstados::PROGRAMADA);

            $em->persist($cirugia);
            $em->flush();

            $this->addFlash('success', 'Cirugía programada exitosamente en la agenda.');

            // Redirect to the daily agenda/dashboard
            return $this->redirectToRoute('app_cirugia_agenda');
        }

        return $this->render('cirugia/programar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/agenda', name: 'app_cirugia_agenda', methods: ['GET'])]
    public function agendaAdmin(CirugiaRepository $cirugiaRepo, EntityManagerInterface $em): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN_QUIROFANO');

        $today = new \DateTime('today');
        $surgeries = $cirugiaRepo->findDailySchedule($today);
        $rooms = $em->getRepository(Quirofano::class)->findAll();

        // 1. Prepare the Grid Grouping
        $grid = ['Pendientes por Asignar Sala' => []];
        foreach ($rooms as $room) {
            $grid[$room->getNombre()] = [];
        }

        // 2. Populate the Grid
        foreach ($surgeries as $cirugia) {
            $roomName = $cirugia->getQuirofano() ? $cirugia->getQuirofano()->getNombre() : 'Pendientes por Asignar Sala';
            $grid[$roomName][] = $cirugia;
        }

        return $this->render('cirugia/agenda_admin.html.twig', [
            'grid' => $grid,
            'today' => $today,
        ]);
    }

    #[Route('/pizarra', name: 'app_cirugia_pizarra', methods: ['GET'])]
    public function pizarraPublica(CirugiaRepository $cirugiaRepo): Response
    {
        // This is a public or read-only view for the waiting room TVs
        $today = new \DateTime('today');
        $surgeries = $cirugiaRepo->findDailySchedule($today);

        return $this->render('cirugia/pizarra_publica.html.twig', [
            'surgeries' => $surgeries,
        ]);
    }

    #[Route('/{id}/avanzar-estado', name: 'app_cirugia_avanzar_estado', methods: ['POST'])]
    public function avanzarEstado(Request $request, Cirugia $cirugia, EntityManagerInterface $em): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN_QUIROFANO');

        $data = json_decode($request->getContent(), true);
        $nextState = $data['estado'] ?? null;
        $now = new \DateTime();

        if ($nextState) {

            switch ($nextState){
                case 'pre_op':
                    $cirugia->setEstado(CirugiaEstados::PRE_OP);
                    $cirugia->setHoraInicioAnestesia($now);
                    break;
                case 'trans_op':
                    $cirugia->setEstado(CirugiaEstados::TRANS_OP);
                    $cirugia->setHoraIncision($now);
                    break;
                case 'post_op':
                    $cirugia->setEstado(CirugiaEstados::POST_OP);
                    $cirugia->setHoraCierre($now);
                    break;
                case 'finalizada':
                    $cirugia->setEstado(CirugiaEstados::FINALIZADA);
                    $cirugia->setHoraSalidaSala($now);
                    break;
            }

            $em->flush();
            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false], 400);
    }

    // 1. SHOW DETAILS
    #[Route('/{id}/ver', name: 'app_cirugia_ver', methods: ['GET'])]
    public function ver(Cirugia $cirugia): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_QUIROFANO');

        return $this->render('cirugia/ver.html.twig', [
            'cirugia' => $cirugia,
        ]);
    }

    // 2. EDIT SCHEDULE
    #[Route('/{id}/editar', name: 'app_cirugia_editar', methods: ['GET', 'POST'])]
    public function editar(Request $request, Cirugia $cirugia, EntityManagerInterface $em): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN_QUIROFANO');

        // Only allow editing if it hasn't started yet!
        if (!in_array($cirugia->getEstado()->value, ['programada', 'pre_op'])) {
            $this->addFlash('warning', 'No se puede editar la logística de una cirugía en curso o finalizada.');
            return $this->redirectToRoute('app_cirugia_agenda');
        }

        $form = $this->createForm(CirugiaType::class, $cirugia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Datos de la cirugía actualizados.');
            return $this->redirectToRoute('app_cirugia_agenda');
        }

        return $this->render('cirugia/programar.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true // Pass this to change the title of your Twig template!
        ]);
    }

    // 3. CANCEL SURGERY (Soft Delete / State Change)
    #[Route('/{id}/cancelar', name: 'app_cirugia_cancelar', methods: ['POST'])]
    public function cancelar(Request $request, Cirugia $cirugia, EntityManagerInterface $em, AuditService $auditService): Response
    {
        // 1. Retrieve the reason sent by the Stimulus controller
        $motivo = $request->request->get('motivo_cancelacion');

        if (!$motivo) {
            $this->addFlash('error', 'El motivo de cancelación es obligatorio.');
            return $this->redirectToRoute('app_cirugia_agenda');
        }

        // 2. Update the entity
        $cirugia->setEstado(CirugiaEstados::CANCELADA);
        $cirugia->setMotivoCancelacion($motivo);

        $message = 'Cirugia cancelada por motivo: ' . $motivo;
        $auditService->persistAudit(
            AuditTipos::SURGERY_CANCELED,
            $message,
            $cirugia->getPaciente(),
            null,
            $cirugia
        );

        // Free the room!
        $cirugia->setQuirofano(null);

        $em->flush();
        $this->addFlash('success', 'La cirugia ha sido cancelada exitosamente.');

        // Redirect back to the pending list
        return $this->redirectToRoute('app_cirugia_agenda');
    }
}
