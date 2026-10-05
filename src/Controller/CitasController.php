<?php

namespace App\Controller;

use App\Entity\Citas;
use App\Entity\Consulta;
use App\Entity\StatusRecord;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use App\Enum\ConsultaEstados;
use App\Enum\ConsultaTipos;
use App\Form\CitasType;
use App\Repository\CitasRepository;
use App\Service\AuditService;
use Composer\XdebugHandler\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citas')]
final class CitasController extends AbstractController
{
    #[Route('/expected', name: 'app_citas_index_expected', methods: ['GET'])]
    public function indexExpected(): Response
    {
        return $this->redirectToRoute('app_citas_index_list', ['state' => 'expected']);
    }

    #[Route('/check-in', name: 'app_citas_index_checkin', methods: ['GET'])]
    public function indexCheckIn(): Response
    {
        return $this->redirectToRoute('app_citas_index_list', ['state' => 'checked_in']);
    }

    #[Route('/complete', name: 'app_citas_index_complete', methods: ['GET'])]
    public function indexCompleted(): Response
    {
        return $this->redirectToRoute('app_citas_index_list', ['state' => 'completed']);
    }

    #[Route('/canceled', name: 'app_citas_index_canceled', methods: ['GET'])]
    public function indexCanceled(): Response
    {
        return $this->redirectToRoute('app_citas_index_list', ['state' => 'canceled']);
    }

    #[Route('/listado', name: 'app_citas_index_list', methods: ['GET', 'POST'])]
    public function index(Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager, \Omines\DataTablesBundle\DataTableFactory $dataTableFactory): Response
    {
        // Default values: today and 'expected' state
        $today = new \DateTime('now');

        $startDate = $request->query->get('startDate')
            ? new \DateTime($request->query->get('startDate'))
            : clone $today->setTime(0, 0, 0);

        $endDate = $request->query->get('endDate')
            ? new \DateTime($request->query->get('endDate'))
            : clone $today->setTime(23, 59, 59);

        $state = $request->query->get('state', \App\Enum\CitasEstados::EXPECTED->value);

        $table = $dataTableFactory->createFromType(\App\DataTable\Type\CitasTableType::class, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'state' => $state,
        ], ['pageLength' => 5])->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        if ($request->query->get('export') === 'pdf') {
            return $this->generatePdfReport($request, $entityManager, $startDate, $endDate, $state);
        }

        return $this->render('citas/index.html.twig', [
            'datatable' => $table,
            'currentState' => $state,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    #[Route('/{id}/show', name: 'app_citas_show', methods: ['GET'])]
    public function show(Citas $cita, EntityManagerInterface $entityManager): Response
    {
        if ($cita->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('danger', 'No se pudo encontrar la inforamcion.');
            return $this->redirectToRoute('app_citas_index_list');
        }

        return $this->render('citas/show.html.twig', [
            'cita' => $cita,
            'paciente' => $cita->getPaciente(),
        ]);
    }

    #[Route('/{id}/check-in', name: 'app_citas_checkin', methods: ['POST'])]
    public function checkIn(Citas $cita, EntityManagerInterface $em, AuditService $auditService): Response {

        $now = new \DateTime('now');

        /*if ($cita->getFecha()->format('Y-m-d') != $now->format('Y-m-d')) {
            $this->addFlash('error', 'No puede anunciar la llegada debido a que este paciente no esta programado para hoy.');
            return $this->redirectToRoute('app_citas_index_list');
        }*/

        // 1. Prevent double check-ins
        if ($cita->getConsulta() !== null) {
            $this->addFlash('warning', 'Este paciente ya fue ingresado a la sala de espera.');
            return $this->redirectToRoute('app_citas_index_list');
        }

        // 2. Change the Appointment Status
        // Assuming you have a CitasEstados Enum.
        $cita->setEstadoCita(CitasEstados::CHECKED_IN);

        // 3. Create the Consultation
        $consulta = new Consulta();
        $consulta->setPaciente($cita->getPaciente());
        $consulta->setFechaInicio(new \DateTime('now'));
        $consulta->setTipoConsulta(ConsultaTipos::CT_GENERAL);
        $consulta->setEstadoConsulta(ConsultaEstados::PENDING);
        $consulta->setEspecialidad($cita->getEspecialidad());
        $consulta->setDoctor($cita->getDoctor());

        // 4. Link them together!
        $cita->setConsulta($consulta);

        // 5. Audit the event using your awesome service
        $auditService->persistAudit(
            AuditTipos::RECEPTION_CHECKIN,
            "Paciente anunciado en recepción. Cita vinculada a una nueva consulta pendiente.",
            $cita->getPaciente(),
            $consulta
        );

        $em->persist($consulta);
        $em->flush();

        $this->addFlash('success', 'Paciente ingresado a la sala de espera exitosamente.');
        return $this->redirectToRoute('app_citas_index_list');
    }

    #[Route('/{id}/canceled', name: 'app_citas_canceled', methods: ['POST'])]
    public function cancel(Request $request, Citas $cita, EntityManagerInterface $em, AuditService $auditService): Response
    {
        // 1. Retrieve the reason sent by the Stimulus controller
        $motivo = $request->request->get('motivo_cancelacion');

        if (!$motivo) {
            $this->addFlash('error', 'El motivo de cancelación es obligatorio.');
            return $this->redirectToRoute('app_citas_index_list');
        }

        // 2. Update the Cita entity
        $cita->setEstadoCita(CitasEstados::CANCELED); // Using your state naming
        $cita->setObservaciones($motivo);

        // Optional but highly recommended: Add an Audit log here!
        $message = 'Cita cancelada por motivo: ' . $motivo;
        $auditService->persistAudit(
            AuditTipos::RECEPTION_CANCELED,
            $message,
            $cita->getPaciente(),
            $cita->getConsulta()
        );

        $em->flush();
        $this->addFlash('success', 'La cita ha sido cancelada exitosamente.');

        // Redirect back to the pending list
        return $this->redirectToRoute('app_citas_index_list');
    }

    private function generatePdfReport(Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager, \DateTime $startDate, \DateTime $endDate, string $state): Response
    {
        $citas = [];
        if ($state === 'all') {
            $citas = $entityManager->getRepository(\App\Entity\Citas::class)->getActivesforTableByDateOnly($startDate, $endDate);
        } else {
            $citas = $entityManager->getRepository(\App\Entity\Citas::class)->getActivesforTableByState($state, $startDate, $endDate);
        }

        if (count($citas) > 1000) {
            $this->addFlash('danger', sprintf('Demasiados registros para exportar (%d). El límite es 1000. Por favor, ajuste los filtros.', count($citas)));
            $params = $request->query->all();
            unset($params['export']);
            return $this->redirectToRoute('app_citas_index_list', $params);
        }

        if (count($citas) === 0) {
            $this->addFlash('warning', 'No hay registros para exportar con los filtros actuales.');
            $params = $request->query->all();
            unset($params['export']);
            return $this->redirectToRoute('app_citas_index_list', $params);
        }

        $html = $this->renderView('citas/pdf_report.html.twig', [
            'citas' => $citas,
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'state' => $state,
            'generatedBy' => $this->getUser(),
            'generationDate' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="reporte_citas.pdf"'
            ]
        );
    }
}



