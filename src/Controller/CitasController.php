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
    #[Route(name: 'app_citas_index', methods: ['GET'])]
    public function index(CitasRepository $citasRepository): Response
    {
        return $this->render('citas/index.html.twig', [
            'citas' => $citasRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_citas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cita = new Citas();
        $form = $this->createForm(CitasType::class, $cita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cita);
            $entityManager->flush();

            return $this->redirectToRoute('app_citas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas/new.html.twig', [
            'cita' => $cita,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_citas_show', methods: ['GET'])]
    public function show(Citas $cita): Response
    {
        return $this->render('citas/show.html.twig', [
            'cita' => $cita,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_citas_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Citas $cita, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CitasType::class, $cita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_citas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas/edit.html.twig', [
            'cita' => $cita,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/check-in', name: 'app_citas_checkin', methods: ['POST'])]
    public function checkIn(Citas $cita, EntityManagerInterface $em, AuditService $auditService): Response {
        // 1. Prevent double check-ins
        if ($cita->getConsulta() !== null) {
            $this->addFlash('warning', 'Este paciente ya fue ingresado a la sala de espera.');
            return $this->redirectToRoute('app_citas_index');
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
        return $this->redirectToRoute('app_citas_index');
    }
}
