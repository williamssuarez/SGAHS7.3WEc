<?php

namespace App\Controller;

use App\Entity\Cirugia;
use App\Entity\Citas;
use App\Entity\ConsumoQuirurgico;
use App\Entity\Hospitalizaciones;
use App\Entity\InventarioLote;
use App\Entity\MovimientoInventario;
use App\Entity\ProtocoloOperatorio;
use App\Entity\Quirofano;
use App\Enum\AuditTipos;
use App\Enum\CirugiaEstados;
use App\Enum\CitasEstados;
use App\Enum\TipoMovimientoInventario;
use App\Form\CirugiaType;
use App\Form\ConsumoQuirurgicoType;
use App\Form\ProtocoloOperatorioType;
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
    public function avanzarEstado(Request $request, Cirugia $cirugia, EntityManagerInterface $em, AuditService $auditService): Response
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

                    $message = 'Cirugia Avanzada a preoperatorio';
                    $auditService->persistAudit(
                        AuditTipos::SURGERY_PRE_OP,
                        $message,
                        $cirugia->getPaciente(),
                        null,
                        $cirugia
                    );
                    break;
                case 'trans_op':
                    $cirugia->setEstado(CirugiaEstados::TRANS_OP);
                    $cirugia->setHoraIncision($now);

                    $message = 'Cirugia Avanzada a transoperatorio';
                    $auditService->persistAudit(
                        AuditTipos::SURGERY_TRANS_OP,
                        $message,
                        $cirugia->getPaciente(),
                        null,
                        $cirugia
                    );
                    break;
                case 'post_op':
                    $cirugia->setEstado(CirugiaEstados::POST_OP);
                    $cirugia->setHoraCierre($now);

                    $message = 'Cirugia Avanzada a postoperatorio';
                    $auditService->persistAudit(
                        AuditTipos::SURGERY_POST_OP,
                        $message,
                        $cirugia->getPaciente(),
                        null,
                        $cirugia
                    );
                    break;
                case 'finalizada':
                    $cirugia->setEstado(CirugiaEstados::FINALIZADA);
                    $cirugia->setHoraSalidaSala($now);

                    $message = 'Cirugia Avanzada a finalizada';
                    $auditService->persistAudit(
                        AuditTipos::SURGERY_FINISHED,
                        $message,
                        $cirugia->getPaciente(),
                        null,
                        $cirugia
                    );
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

        $consumo = new ConsumoQuirurgico();
        $consumoForm = $this->createForm(ConsumoQuirurgicoType::class, $consumo);

        $protocolo = new ProtocoloOperatorio();
        $protocoloForm = $this->createForm(ProtocoloOperatorioType::class, $protocolo);

        return $this->render('cirugia/ver.html.twig', [
            'cirugia' => $cirugia,
            'consumoForm' => $consumoForm->createView(),
            'protocoloForm' => $protocoloForm->createView(),
        ]);
    }

    #[Route('/{id}/registrar-consumo', name: 'app_cirugia_registrar_consumo', methods: ['POST'])]
    public function registrarConsumo(Request $request, Cirugia $cirugia, EntityManagerInterface $em): Response
    {
        $consumo = new ConsumoQuirurgico();
        $form = $this->createForm(ConsumoQuirurgicoType::class, $consumo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Set the automatic audit and timestamp fields
            $consumo->setCirugia($cirugia);

            // 2. THE INVENTORY DEDUCTION ENGINE (Only if hospital provided it)
            if (!$consumo->isAportadoPorPaciente()) {

                $cantidadRequerida = $consumo->getCantidad();
                $articulo = $consumo->getArticuloInventario();

                // Fetch batches for this item that have stock, ordered by expiration date (FIFO)
                $lotesDisponibles = $em->getRepository(InventarioLote::class)->createQueryBuilder('l')
                    ->where('l.articulo = :articulo')
                    ->andWhere('l.cantidadActual > 0')
                    ->setParameter('articulo', $articulo)
                    ->orderBy('l.fechaCaducidad', 'ASC') // Closest to expiring first!
                    ->getQuery()
                    ->getResult();

                // Check if we even have enough total stock before modifying anything
                $stockTotal = array_reduce($lotesDisponibles, fn($sum, $lote) => $sum + $lote->getCantidadActual(), 0);

                if ($stockTotal < $cantidadRequerida) {
                    $this->addFlash('danger', 'No hay stock suficiente en inventario para ' . $articulo->getNombre() . '. Stock actual: ' . $stockTotal);
                    // Redirect back to the surgery view with the consumos tab open
                    return $this->redirect($this->generateUrl('app_cirugia_ver', ['id' => $cirugia->getId()]) . '#consumos');
                }

                // Loop through batches and deduct stock
                foreach ($lotesDisponibles as $lote) {
                    if ($cantidadRequerida <= 0) {
                        break; // We have fulfilled the requested amount
                    }

                    $stockEnLote = $lote->getCantidadActual();
                    $cantidadADescontar = min($stockEnLote, $cantidadRequerida);

                    // Update the physical batch
                    $lote->setCantidadActual($stockEnLote - $cantidadADescontar);

                    // Create the Audit Trail Movement for this specific batch
                    $movimiento = new MovimientoInventario();
                    $movimiento->setInventarioLote($lote);
                    $movimiento->setTipoMovimiento(TipoMovimientoInventario::SALIDA);
                    $movimiento->setCantidad(-$cantidadADescontar); // Negative because it's leaving
                    $movimiento->setReferenciaOrigen('Kardex Quirúrgico - Cirugía ID: ' . $cirugia->getId());

                    $em->persist($movimiento);

                    // Reduce the remaining amount we need to find
                    $cantidadRequerida -= $cantidadADescontar;
                }
            }

            // 3. Save the Kardex entry itself
            $em->persist($consumo);
            $em->flush();

            $this->addFlash('success', 'Insumo registrado correctamente en el Kardex.');
        } else {
            // If someone bypassed the JS validation
            $this->addFlash('error', 'Error en el formulario. Verifique los datos ingresados.');
        }

        // Redirect back to the surgery view, focusing on the Consumos tab
        return $this->redirect($this->generateUrl('app_cirugia_ver', ['id' => $cirugia->getId()]) . '#consumos');
    }

    #[Route('/{id}/redactar-protocolo', name: 'app_cirugia_redactar_protocolo', methods: ['POST'])]
    public function redactarProtocolo(Request $request, Cirugia $cirugia, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DOCTOR');

        // Security Check: Only allow writing if the surgery actually started or finished
        if (in_array($cirugia->getEstado()->value, ['programada', 'cancelada'])) {
            $this->addFlash('error', 'No puede redactar un protocolo para una cirugía que no se ha realizado.');
            return $this->redirect($this->generateUrl('app_cirugia_ver', ['id' => $cirugia->getId()]) . '#protocolo');
        }

        $protocolo = $cirugia->getProtocoloOperatorio() ?? new ProtocoloOperatorio();

        $form = $this->createForm(ProtocoloOperatorioType::class, $protocolo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // If it's a new protocol, set the immutable data
            if (!$protocolo->getId()) {
                $protocolo->setCirugia($cirugia);
                $cirugia->setProtocoloOperatorio($protocolo);
            }

            $em->persist($protocolo);
            $em->flush();

            $this->addFlash('success', 'El Protocolo Operatorío ha sido firmado y guardado exitosamente.');
        }

        return $this->redirect($this->generateUrl('app_cirugia_ver', ['id' => $cirugia->getId()]) . '#protocolo');
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
