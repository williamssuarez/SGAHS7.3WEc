<?php

namespace App\Controller;

use App\Entity\CitasConfiguraciones;
use App\Entity\CitasSolicitudes;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Enum\CitasSolicitudesEstados;
use App\Form\CitasSolicitudesType;
use App\Repository\CitasSolicitudesRepository;
use App\Service\AppointmentScheduler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Citas;

#[Route('/citas/solicitudes')]
final class CitasSolicitudesController extends AbstractController
{
    #[Route(name: 'app_citas_solicitudes_index', methods: ['GET'])]
    public function index(CitasSolicitudesRepository $citasSolicitudesRepository): Response
    {
        return $this->render('citas_solicitudes/index.html.twig', [
            'entities' => $citasSolicitudesRepository->getActivesforTableByPaciente($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_citas_solicitudes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $citasSolicitude = new CitasSolicitudes();
        $form = $this->createForm(CitasSolicitudesType::class, $citasSolicitude);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userEmail = $this->getUser()->getUserIdentifier();
            $userObj = $entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
            $paciente = $userObj->getExternalProfile()->getPaciente();

            $citasSolicitude->setPaciente($paciente);
            $entityManager->persist($citasSolicitude);
            $entityManager->flush();

            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/new.html.twig', [
            'entity' => $citasSolicitude,
            'form' => $form,
        ]);
    }

    #[Route('/admin/citas/test-engine', name: 'app_admin_citas_test_engine', methods: ['POST'])]
    public function testEngine(EntityManagerInterface $em, AppointmentScheduler $scheduler): Response {
        // Find all configurations
        $configs = $em->getRepository(CitasConfiguraciones::class)->findAll();

        // Let's test scheduling for tomorrow
        $targetDate = new \DateTime('+1 day');
        $totalAssigned = 0;

        foreach ($configs as $config) {
            // Process the queue for each active specialty configuration
            $assigned = $scheduler->processQueue($config, $targetDate);
            $totalAssigned += $assigned;
        }

        if ($totalAssigned > 0) {
            $this->addFlash('success', "¡Motor ejecutado con éxito! Se asignaron $totalAssigned citas para el " . $targetDate->format('d/m/Y') . ".");
        } else {
            $this->addFlash('info', 'El motor se ejecutó, pero no había solicitudes pendientes o no hay cupos disponibles.');
        }

        // Redirect back to wherever your admin dashboard or config index is
        return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_citas_solicitudes_show', methods: ['GET'])]
    public function show(CitasSolicitudes $citasSolicitude, EntityManagerInterface $entityManager): Response
    {
        $cita = null;
        if ($citasSolicitude->getEstadoSolicitud() == CitasSolicitudesEstados::SCHEDULED){
            $cita = $entityManager->getRepository(Citas::class)->findOneBy([
                'solicitud' => $citasSolicitude,
                'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
            ]);
        }
        return $this->render('citas_solicitudes/show.html.twig', [
            'entity' => $citasSolicitude,
            'cita' => $cita,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_citas_solicitudes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CitasSolicitudes $citasSolicitude, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CitasSolicitudesType::class, $citasSolicitude);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/edit.html.twig', [
            'citas_solicitude' => $citasSolicitude,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_citas_solicitudes_delete', methods: ['POST'])]
    public function delete(Request $request, CitasSolicitudes $citasSolicitude, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$citasSolicitude->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($citasSolicitude);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
    }
}
