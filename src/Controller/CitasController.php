<?php

namespace App\Controller;

use App\Entity\Citas;
use App\Entity\Consulta;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use App\Enum\ConsultaEstados;
use App\Enum\ConsultaTipos;
use App\Form\CitasType;
use App\Repository\CitasRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citas')]
final class CitasController extends AbstractController
{
    #[Route('/expected', name: 'app_citas_index_expected', methods: ['GET'])]
    public function indexExpected(CitasRepository $citasRepository): Response
    {
        $now = new \DateTime('+1 day');
        $from = clone $now->setTime(0, 0, 0);
        $to = clone $now->setTime(23, 59, 59);

        return $this->render('citas/index.html.twig', [
            'entities' => $citasRepository->getActivesforTableByState(CitasEstados::EXPECTED, $from, $to),
            'stateType' => 'Pendientes'
        ]);
    }

    #[Route('/check-in', name: 'app_citas_index_checkin', methods: ['GET'])]
    public function indexCheckIn(CitasRepository $citasRepository): Response
    {
        $now = new \DateTime('+1 day');
        $from = clone $now->setTime(0, 0, 0);
        $to = clone $now->setTime(23, 59, 59);

        return $this->render('citas/index.html.twig', [
            'entities' => $citasRepository->getActivesforTableByState(CitasEstados::CHECKED_IN, $from, $to),
            'stateType' => 'En Espera'
        ]);
    }

    #[Route('/complete', name: 'app_citas_index_complete', methods: ['GET'])]
    public function indexCompleted(CitasRepository $citasRepository): Response
    {
        $now = new \DateTime('+1 day');
        $from = clone $now->setTime(0, 0, 0);
        $to = clone $now->setTime(23, 59, 59);

        return $this->render('citas/index.html.twig', [
            'entities' => $citasRepository->getActivesforTableByState(CitasEstados::COMPLETED, $from, $to),
            'stateType' => 'Finalizadas'
        ]);
    }

    #[Route('/canceled', name: 'app_citas_index_canceled', methods: ['GET'])]
    public function indexCanceled(CitasRepository $citasRepository): Response
    {
        $now = new \DateTime('+1 day');
        $from = clone $now->setTime(0, 0, 0);
        $to = clone $now->setTime(23, 59, 59);

        return $this->render('citas/index.html.twig', [
            'entities' => $citasRepository->getActivesforTableByState(CitasEstados::CANCELED, $from, $to),
            'stateType' => 'Canceladas'
        ]);
    }

    #[Route('/{id}/show', name: 'app_citas_show', methods: ['GET'])]
    public function show(Citas $cita): Response
    {
        return $this->render('citas/show.html.twig', [
            'cita' => $cita,
        ]);
    }

    #[Route('/{id}/check-in', name: 'app_citas_checkin', methods: ['POST'])]
    public function checkIn(Citas $cita, EntityManagerInterface $em, AuditService $auditService): Response {
        // 1. Prevent double check-ins
        if ($cita->getConsulta() !== null) {
            $this->addFlash('warning', 'Este paciente ya fue ingresado a la sala de espera.');
            return $this->redirectToRoute('app_citas_index_expected');
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
        return $this->redirectToRoute('app_citas_index_expected');
    }

    #[Route('/{id}/canceled', name: 'app_citas_canceled', methods: ['POST'])]
    public function cancel(Request $request, Citas $cita, EntityManagerInterface $em, AuditService $auditService): Response
    {
        // 1. Retrieve the reason sent by the Stimulus controller
        $motivo = $request->request->get('motivo_cancelacion');

        if (!$motivo) {
            $this->addFlash('error', 'El motivo de cancelación es obligatorio.');
            return $this->redirectToRoute('app_citas_index_expected');
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
        return $this->redirectToRoute('app_citas_index_expected');
    }
}
