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

    #[Route('/historial', name: 'app_visita_hospitalaria_historial', methods: ['GET', 'POST'])]
    public function historial(Request $request, \Omines\DataTablesBundle\DataTableFactory $dataTableFactory): Response
    {
        $startDate = $request->query->get('start_date')
            ? new \DateTime($request->query->get('start_date'))
            : new \DateTime('today');
        $endDate = $request->query->get('end_date')
            ? new \DateTime($request->query->get('end_date'))
            : new \DateTime('today');

        $table = $dataTableFactory->createFromType(\App\DataTable\Type\VisitaHospitalariaTableType::class, [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ], ['pageLength' => 5])->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('visita_hospitalaria/historial.html.twig', [
            'datatable' => $table,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    #[Route('/pre-registrar', name: 'app_visitas_pre_registrar', methods: ['POST'])]
    public function preRegistrar(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECEPTIONIST');
        
        $pacienteId = $request->request->get('paciente_id');
        if (!$pacienteId) {
            $this->addFlash('danger', 'Debe seleccionar un paciente válido.');
            return $this->redirectToRoute('app_visita_hospitalaria_index');
        }

        $hospitalizacion = $em->getRepository(Hospitalizaciones::class)->findOneBy([
            'paciente' => $pacienteId,
            'estado' => HospitalizacionEstados::ADMITTED,
            'status' => $em->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$hospitalizacion) {
            $this->addFlash('danger', 'El paciente seleccionado no se encuentra hospitalizado actualmente.');
            return $this->redirectToRoute('app_visita_hospitalaria_index');
        }

        return $this->redirectToRoute('app_visitas_registrar', ['id' => $hospitalizacion->getId()]);
    }

    #[Route('/{id<\d+>}/registrar-visita', name: 'app_visitas_registrar', methods: ['GET', 'POST'])]
    public function registrarVisita(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em, \App\Repository\HorarioVisitasRepository $horarioRepository): Response
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
        $area = $hospitalizacion->getCamaActual()->getHabitacion()->getArea();
        $isWithinHours = $horarioRepository->isAreaOpenForVisits($area, new \DateTime());
        
        if (!$isWithinHours) {
            $this->addFlash('warning', 'Fuera de horario de visitas para el área de ' . $area->getNombre());
            return $this->redirectToRoute('app_visita_hospitalaria_index');
        }

        $visitaHospitalarium = new VisitaHospitalaria();
        $visitaHospitalarium->setHospitalizacion($hospitalizacion);
        
        $form = $this->createForm(VisitaHospitalariaType::class, $visitaHospitalarium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $visitaHospitalarium->setEstado('ACTIVA');
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

    #[Route('/{id<\d+>}/marcar-salida', name: 'app_visitas_marcar_salida', methods: ['POST'])]
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

    #[Route('/{id<\d+>}', name: 'app_visita_hospitalaria_show', methods: ['GET'])]
    public function show(VisitaHospitalaria $visitaHospitalarium): Response
    {
        return $this->render('visita_hospitalaria/show.html.twig', [
            'visita_hospitalarium' => $visitaHospitalarium,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_visita_hospitalaria_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id<\d+>}', name: 'app_visita_hospitalaria_delete', methods: ['POST'])]
    public function delete(Request $request, VisitaHospitalaria $visitaHospitalarium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$visitaHospitalarium->getId(), $request->getPayload()->getString('_token'))) {
            $status = $entityManager->getRepository(StatusRecord::class)->getRemove();
            $visitaHospitalarium->setStatus($status);
            $entityManager->persist($visitaHospitalarium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visita_hospitalaria_index', [], Response::HTTP_SEE_OTHER);
    }
}



