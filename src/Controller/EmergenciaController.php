<?php

namespace App\Controller;

use App\Entity\Discapacidades;
use App\Entity\Emergencia;
use App\Entity\EvolucionEmergencia;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use App\Enum\CamaEstados;
use App\Enum\EmergenciasEstados;
use App\Form\AsignarCamaType;
use App\Form\AsociarPacienteType;
use App\Form\DiscapacidadesType;
use App\Form\EmergenciaIngresoType;
use App\Form\EmergenciaTriageType;
use App\Form\EvolucionEmergenciaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/emergencia')]
final class EmergenciaController extends AbstractController
{
    #[Route(name: 'app_emergencia_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // 1. Pass the actual Enum cases to findBy, let Doctrine handle the string conversion
        $activas = $em->getRepository(Emergencia::class)->findBy([
            'estado' => [
                EmergenciasEstados::WAITING_TRIAGE,
                EmergenciasEstados::WAITING_BED,
                EmergenciasEstados::IN_TREATMENT
            ],
            'status' => $em->getRepository(StatusRecord::class)->getActive()
            ], ['fechaIngreso' => 'DESC']
        );

        // 2. These keys are EXACTLY what Twig expects: emergencias['WAITING_TRIAGE']
        $emergencias = [
            'WAITING_TRIAGE' => [],
            'WAITING_BED' => [],
            'IN_TREATMENT' => []
        ];

        foreach ($activas as $emergencia) {
            // ->name extracts the uppercase constant name (e.g., 'WAITING_TRIAGE')
            // regardless of whether the database value is lowercase.
            $estadoName = $emergencia->getEstado()->name;

            if (isset($emergencias[$estadoName])) {
                $emergencias[$estadoName][] = $emergencia;
            }
        }

        return $this->render('emergencia/index.html.twig', [
            'emergencias' => $emergencias,
        ]);
    }

    #[Route('/new-ingreso', name: 'app_emergencia_new', methods: ['GET', 'POST'])]
    public function newIngreso(Request $request, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        $emergencia = new Emergencia();
        $form = $this->createForm(EmergenciaIngresoType::class, $emergencia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emergencia->setEstado(EmergenciasEstados::WAITING_TRIAGE);
            $entityManager->persist($emergencia);
            $entityManager->flush();

            // 1. Render the HTML fragment we just created
            $html = $this->renderView('emergencia/tableRows/_new_er_table_row.html.twig', [
                'emergencia' => $emergencia
            ]);

            // 2. Publish to Mercure on the topic "emergencias"
            $update = new Update(
                'emergencias',
                json_encode([
                    'estado' => EmergenciasEstados::WAITING_TRIAGE->value,
                    'html' => $html
                ])
            );
            $hub->publish($update);

            $this->addFlash('success', 'Emergencia Agregada.');
            return $this->redirectToRoute('app_emergencia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emergencia/newIngreso.html.twig', [
            'entity' => $emergencia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_emergencia_paciente_show', methods: ['GET', 'POST'])]
    public function pacienteShow(Request $request, Emergencia $emergencia, EntityManagerInterface $entityManager): Response
    {
        $evolucion = new EvolucionEmergencia();
        $form = $this->createForm(EvolucionEmergenciaType::class, $evolucion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evolucion->setEmergencia($emergencia);

            $entityManager->persist($evolucion);
            $entityManager->flush();

            $this->addFlash('success', 'Evolución clínica agregada correctamente.');
            return $this->redirectToRoute('app_emergencia_paciente_show', ['id' => $emergencia->getId()]);
        }

        return $this->render('emergencia/pacienteShow.html.twig', [
            'entity' => $emergencia,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/asociar-paciente', name: 'app_emergencia_associate_patient', methods: ['GET', 'POST'])]
    public function associatePatient(Request $request, Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub): Response
    {
        $form = $this->createForm(AsociarPacienteType::class, $emergencia, [
            'action' => $this->generateUrl('app_emergencia_associate_patient', ['id' => $emergencia->getId()]),
        ]);

        $form->handleRequest($request);

        //@TODO: Fix csrf token for ajax forms
        if ($form->isSubmitted()) { // Assuming you fixed or bypassed the CSRF!
            // Optional: You can clear the temporal name, or leave it as historical data.
            //$emergencia->setPacienteTemporal(null);

            $em->persist($emergencia);
            $em->flush();

            // Figure out which template to render based on current state
            $template = match($emergencia->getEstado()) {
                EmergenciasEstados::WAITING_TRIAGE => 'emergencia/tableRows/_new_er_table_row.html.twig',
                EmergenciasEstados::WAITING_BED => 'emergencia/tableRows/_new_triage_table.row.html.twig',
                EmergenciasEstados::IN_TREATMENT => 'emergencia/tableRows/_new_treatment_table_row.html.twig',
                default => null
            };

            if ($template) {
                $html = $this->renderView($template, ['emergencia' => $emergencia]);

                // Push update to Mercure. The JS will automatically update the row in place!
                $update = new Update(
                    'emergencias',
                    json_encode([
                        'id' => $emergencia->getId(),
                        'estado' => $emergencia->getEstado()->name,
                        'html' => $html
                    ])
                );
                $hub->publish($update);
            }

            return $this->json(['success' => true, 'message' => 'Paciente vinculado exitosamente.']);
        }

        return $this->render('emergencia/forms/_associate_patient_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/triage', name: 'app_emergencia_triage_new', methods: ['GET', 'POST'])]
    public function triage(Request $request, Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub): Response
    {
        if ($emergencia->getEstado() !== EmergenciasEstados::WAITING_TRIAGE) {
            $this->addFlash('warning', 'Esta emergencia ya superó la etapa de triage.');
            return $this->redirectToRoute('app_emergencia_index');
        }

        $triage = new Triage();
        $form = $this->createForm(EmergenciaTriageType::class, $triage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emergencia->setTriage($triage);
            $emergencia->setEstado(EmergenciasEstados::WAITING_BED);

            $em->persist($triage);
            $em->persist($emergencia);
            $em->flush();

            $html = $this->renderView('emergencia/tableRows/_new_triage_table.row.html.twig', [
                'emergencia' => $emergencia
            ]);

            $update = new Update(
                'emergencias',
                json_encode([
                    'id' => $emergencia->getId(),
                    'estado' => EmergenciasEstados::WAITING_BED->value, // e.g., 'waiting_bed'
                    'html' => $html
                ])
            );
            $hub->publish($update);

            $this->addFlash('success', 'Triaje registrado. Paciente en espera de cama.');
            return $this->redirectToRoute('app_emergencia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emergencia/newTriage.html.twig', [
            'emergencia' => $emergencia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/triage-details', name: 'app_emergencia_triage_details', methods: ['GET'])]
    public function triageDetails(Emergencia $emergencia): Response
    {
        // Make sure we actually have a triage to show
        if (!$emergencia->getTriage()) {
            return new Response('<p class="text-danger">No hay datos de triage disponibles para este paciente.</p>');
        }

        return $this->render('emergencia/triageDetails.html.twig', [
            'emergencia' => $emergencia,
            'triage' => $emergencia->getTriage(),
        ]);
    }

    #[Route('/{id}/asignar-cama', name: 'app_emergencia_assign_bed', methods: ['GET', 'POST'])]
    public function assignBed(Request $request, Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($emergencia->getEstado() !== EmergenciasEstados::WAITING_BED) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'error' => 'Estado inválido para asignar cama.']);
            }
            $this->addFlash('danger', 'Estado inválido para asignar cama.');
            return $this->redirectToRoute('app_emergencia_index');
        }

        $form = $this->createForm(AsignarCamaType::class, $emergencia, [
            'action' => $this->generateUrl('app_emergencia_assign_bed', ['id' => $emergencia->getId()]),
        ]);

        $form->handleRequest($request);

        // @TODO: Fix AJAX CSRF
        if ($form->isSubmitted()) {
            $cama = $emergencia->getCamaActual();

            if ($cama->getEstado() !== CamaEstados::AVAILABLE) {
                return $this->json(['success' => false, 'error' => 'Esta cama acaba de ser ocupada.']);
            }

            $emergencia->setEstado(EmergenciasEstados::IN_TREATMENT);
            $cama->setEstado(CamaEstados::OCUPIED);

            $em->persist($emergencia);
            $em->persist($cama);
            $em->flush();

            $html = $this->renderView('emergencia/tableRows/_new_treatment_table_row.html.twig', ['emergencia' => $emergencia]);

            $update = new Update(
                'emergencias',
                json_encode([
                    'id' => $emergencia->getId(),
                    'estado' => EmergenciasEstados::IN_TREATMENT->value,
                    'html' => $html
                ])
            );
            $hub->publish($update);

            return $this->json(['success' => true, 'message' => 'Cama asignada exitosamente.']);
        }

        // THE FIX: Manually generate the token using the exact name the form component uses
        $tokenId = $form->getName(); // Usually 'asignar_cama'
        $tokenValue = $csrfTokenManager->getToken($tokenId)->getValue();

        // Pass the raw string directly to Twig
        return $this->render('emergencia/forms/_assign_bed_form.html.twig', [
            'form' => $form->createView(),
            'manual_csrf_token' => $tokenValue, // <--- Passing it here
        ]);
    }
}
