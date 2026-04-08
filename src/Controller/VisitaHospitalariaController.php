<?php

namespace App\Controller;

use App\Entity\Hospitalizaciones;
use App\Entity\StatusRecord;
use App\Entity\VisitaHospitalaria;
use App\Enum\HospitalizacionEstados;
use App\Form\VisitaHospitalariaType;
use App\Repository\VisitaHospitalariaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/visita/hospitalaria')]
final class VisitaHospitalariaController extends AbstractController
{
    #[Route(name: 'app_visita_hospitalaria_index', methods: ['GET'])]
    public function index(VisitaHospitalariaRepository $visitaHospitalariaRepository, EntityManagerInterface $entityManager): Response
    {
        return $this->render('visita_hospitalaria/index.html.twig', [
            'entities' => $visitaHospitalariaRepository->findBy([
                'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
                'estado' => 'ACTIVA'
            ]),
        ]);
    }

    #[Route('/new', name: 'app_visita_hospitalaria_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $visitaHospitalarium = new VisitaHospitalaria();
        $form = $this->createForm(VisitaHospitalariaType::class, $visitaHospitalarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($visitaHospitalarium);
            $entityManager->flush();

            return $this->redirectToRoute('app_visita_hospitalaria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visita_hospitalaria/new.html.twig', [
            'visita_hospitalarium' => $visitaHospitalarium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/registrar-visita', name: 'app_visitas_registrar', methods: ['GET', 'POST'])]
    public function registrarVisita(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECEPTIONIST');

        // 1. CHECK: Is the patient actually admitted?
        if ($hospitalizacion->getEstado() !== HospitalizacionEstados::ADMITTED) {
            $this->addFlash('danger', 'Este paciente no se encuentra hospitalizado actualmente.');
            return $this->redirectToRoute('app_visita_hospitalaria_index');
        }

        // 2. CHECK: Does the doctor allow visits?
        if (!$hospitalizacion->isVisitasPermitidas()) {
            $nota = $hospitalizacion->getNotaRestriccionVisitas() ?: 'Restricción médica general.';
            $this->addFlash('danger', 'VISITAS RESTRINGIDAS: ' . $nota);
            return $this->redirectToRoute('app_visita_hospitalaria_index');
        }

        // 3. CHECK: Are we within visiting hours?
        // (Assuming you have a service or repository method to check the HorarioVisitas table)
        $area = $hospitalizacion->getCamaActual()->getHabitacion()->getArea();
        /* $isWithinHours = $horarioService->checkIfVisitingHours($area, new \DateTime());
        if (!$isWithinHours) {
            $this->addFlash('warning', 'Fuera de horario de visitas para el área de ' . $area->getNombre());
            return $this->redirectToRoute('app_recepcion_dashboard');
        }
        */

        $visitaHospitalarium = new VisitaHospitalaria();
        $form = $this->createForm(VisitaHospitalariaType::class, $visitaHospitalarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visitaHospitalarium->setEstado('ACTIVA');
            $visitaHospitalarium->setHospitalizacion($hospitalizacion);
            $visitaHospitalarium->setFechaHoraEntrada(new \DateTime());
            $em->persist($visitaHospitalarium);
            $em->flush();

            $this->addFlash('success', 'Visita registrada. Entregar pase de visitante.');
            return $this->redirectToRoute('app_visita_hospitalaria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visita_hospitalaria/new.html.twig', [
            'entity' => $visitaHospitalarium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/marcar-salida', name: 'app_visitas_marcar_salida', methods: ['POST'])]
    public function marcarSalida(Request $request, VisitaHospitalaria $visita, EntityManagerInterface $em): Response
    {
        // 1. Security Check
        $this->denyAccessUnlessGranted('ROLE_RECEPTIONIST');

        // 2. Validate CSRF Token
        if ($this->isCsrfTokenValid('checkout' . $visita->getId(), $request->request->get('_token'))) {

            // Prevent checking out someone who already left
            if ($visita->getEstado() === 'FINALIZADA') {
                $this->addFlash('warning', 'La salida de este visitante ya había sido registrada.');
            } else {
                // 3. Close the visit
                $visita->setEstado('FINALIZADA');
                $visita->setFechaHoraSalida(new \DateTime());

                $em->flush();

                $this->addFlash('success', 'Salida registrada correctamente. Pase de visitante invalidado.');
            }
        } else {
            $this->addFlash('danger', 'Token de seguridad inválido.');
        }

        // 4. Redirect back to the active dashboard
        return $this->redirectToRoute('app_visita_hospitalaria_index');
    }

    #[Route('/{id}', name: 'app_visita_hospitalaria_show', methods: ['GET'])]
    public function show(VisitaHospitalaria $visitaHospitalarium): Response
    {
        return $this->render('visita_hospitalaria/show.html.twig', [
            'visita_hospitalarium' => $visitaHospitalarium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visita_hospitalaria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VisitaHospitalaria $visitaHospitalarium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VisitaHospitalariaType::class, $visitaHospitalarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visita_hospitalaria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visita_hospitalaria/edit.html.twig', [
            'visita_hospitalarium' => $visitaHospitalarium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visita_hospitalaria_delete', methods: ['POST'])]
    public function delete(Request $request, VisitaHospitalaria $visitaHospitalarium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visitaHospitalarium->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visitaHospitalarium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visita_hospitalaria_index', [], Response::HTTP_SEE_OTHER);
    }
}
