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
    public function index(CitasSolicitudesRepository $citasSolicitudesRepository, EntityManagerInterface $entityManager): Response
    {
        $userEmail = $this->getUser()->getUserIdentifier();
        $userObj = $entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail, 'status' => $entityManager->getRepository(StatusRecord::class)->getActive()]);
        $paciente = $userObj->getExternalProfile()->getPaciente();

        return $this->render('citas_solicitudes/index.html.twig', [
            'entities' => $citasSolicitudesRepository->getActivesforTableByPaciente($paciente->getId()),
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

            $this->addFlash('success', 'La solicitud de cita ha sido ingresada. Espere mientras es asignado una fecha.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/new.html.twig', [
            'entity' => $citasSolicitude,
            'form' => $form,
        ]);
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

            $this->addFlash('success', 'La solicitud de cita ha sido editada. Espere mientras es asignado una fecha.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/edit.html.twig', [
            'entity' => $citasSolicitude,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_citas_solicitudes_cancelar', methods: ['POST'])]
    public function cancel(CitasSolicitudes $citasSolicitudes, EntityManagerInterface $em): Response
    {
        $citasSolicitudes->setEstadoSolicitud(CitasSolicitudesEstados::CANCELED);
        $em->persist($citasSolicitudes);

        $em->flush();

        $this->addFlash('success', 'La solicitud de cita ha sido cancelada.');
        return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
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
