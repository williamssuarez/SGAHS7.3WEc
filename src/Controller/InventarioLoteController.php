<?php

namespace App\Controller;

use App\Entity\InventarioLote;
use App\Entity\MovimientoInventario;
use App\Enum\AuditTipos;
use App\Enum\TipoMovimientoInventario;
use App\Form\InventarioLoteEditType;
use App\Form\InventarioLoteType;
use App\Form\InventarioMovimientoAjusteType;
use App\Repository\InventarioLoteRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventario/lote')]
final class InventarioLoteController extends AbstractController
{
    #[Route(name: 'app_inventario_lote_index', methods: ['GET'])]
    public function index(InventarioLoteRepository $inventarioLoteRepository): Response
    {
        return $this->render('inventario_lote/index.html.twig', [
            'inventario_lotes' => $inventarioLoteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_inventario_lote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_PHARMACY'); // Or ROLE_ADMIN

        $lote = new InventarioLote();
        $form = $this->createForm(InventarioLoteType::class, $lote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Create the Audit Trail Movement AUTOMATICALLY
            $movimiento = new MovimientoInventario();
            $movimiento->setInventarioLote($lote);
            $movimiento->setTipoMovimiento(TipoMovimientoInventario::ENTRADA);
            $movimiento->setCantidad($lote->getCantidadActual()); // The initial stock
            $movimiento->setReferenciaOrigen('Ingreso Inicial de Lote');

            // 2. Persist both the Batch and the Movement
            $em->persist($lote);
            $em->persist($movimiento);
            $em->flush();

            $this->addFlash('success', 'Lote ingresado y movimiento registrado en el historial correctamente.');
            return $this->redirectToRoute('app_inventario_lote_index');
        }

        return $this->render('inventario_lote/new.html.twig', [
            'lote' => $lote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_inventario_lote_show', methods: ['GET'])]
    public function show(InventarioLote $inventarioLote): Response
    {
        return $this->render('inventario_lote/show.html.twig', [
            'lote' => $inventarioLote,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inventario_lote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InventarioLote $inventarioLote, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $form = $this->createForm(InventarioLoteEditType::class, $inventarioLote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movimiento = new MovimientoInventario();
            $movimiento->setInventarioLote($inventarioLote);
            $movimiento->setTipoMovimiento(TipoMovimientoInventario::EDICION);
            $movimiento->setCantidad($inventarioLote->getCantidadActual()); // The initial stock
            $movimiento->setReferenciaOrigen('Edicion de datos del Lote');

            $entityManager->persist($movimiento);

            $auditService->persistEditionAudit(
                $inventarioLote,
                AuditTipos::INVENTORY_BATCH_EDITION,
                null,
                null,
                null,
                null,
                null,
                $inventarioLote,
            );

            $entityManager->flush();

            return $this->redirectToRoute('app_inventario_lote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inventario_lote/edit.html.twig', [
            'inventario_lote' => $inventarioLote,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/ajustar', name: 'app_inventario_lote_ajuste', methods: ['GET', 'POST'])]
    public function ajustar(Request $request, InventarioLote $lote, EntityManagerInterface $em, AuditService $auditService): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_PHARMACY');

        // Initialize the new movement
        $ajuste = new MovimientoInventario();
        $form = $this->createForm(InventarioMovimientoAjusteType::class, $ajuste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cantidadAjuste = $ajuste->getCantidad();
            $nuevoStock = $lote->getCantidadActual() + $cantidadAjuste;

            // Backend safety net: Prevent negative stock
            if ($nuevoStock < 0) {
                $this->addFlash('error', 'El ajuste resulta en un stock negativo. Operación cancelada.');
                return $this->redirectToRoute('app_inventario_lote_ajuste', ['id' => $lote->getId()]);
            }

            // 1. Update the physical batch
            $lote->setCantidadActual($nuevoStock);

            // 2. Finalize the audit record
            $ajuste->setInventarioLote($lote);
            $ajuste->setTipoMovimiento(TipoMovimientoInventario::AJUSTE);
            // The 'cantidad' and 'referenciaOrigen' are already populated by the form

            $em->persist($ajuste);
            $em->flush();

            $this->addFlash('success', 'El inventario ha sido ajustado y auditado correctamente.');
            return $this->redirectToRoute('app_inventario_lote_show', ['id' => $lote->getId()]);
        }

        return $this->render('inventario_lote/ajustar.html.twig', [
            'lote' => $lote,
            'form' => $form->createView(),
        ]);
    }
}
