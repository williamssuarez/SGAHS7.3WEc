<?php

namespace App\Controller;

use App\Entity\AltaMedica;
use App\Entity\Discapacidades;
use App\Entity\Emergencia;
use App\Entity\EvolucionEmergencia;
use App\Entity\Hospitalizaciones;
use App\Entity\MainConfiguration;
use App\Entity\StatusRecord;
use App\Entity\Triage;
use App\Enum\AuditTipos;
use App\Enum\CamaEstados;
use App\Enum\EmergenciasCondicionAlta;
use App\Enum\EmergenciasEstados;
use App\Enum\HospitalizacionEstados;
use App\Form\AltaMedicaType;
use App\Form\AsignarCamaType;
use App\Form\AsociarPacienteType;
use App\Form\DiscapacidadesType;
use App\Form\EditTemporaryNameType;
use App\Form\EmergenciaIngresoType;
use App\Form\EmergenciaTriageType;
use App\Form\EvolucionEmergenciaType;
use App\Repository\EmergenciaRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

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

    #[Route('/listado', name: 'app_emergencia_listado', methods: ['GET'])]
    public function listado(Request $request, EmergenciaRepository $emergenciaRepository): Response
    {
        // Default values: today and 'expected' state
        $today = new \DateTime('now');

        $startDate = $request->query->get('startDate')
            ? new \DateTime($request->query->get('startDate'))
            : clone $today->setTime(0, 0, 0);

        $endDate = $request->query->get('endDate')
            ? new \DateTime($request->query->get('endDate'))
            : clone $today->setTime(23, 59, 59);

        $state = $request->query->get('state', EmergenciasCondicionAlta::SENT_HOME->value);

        if ($state == 'all'){
            $entities = $emergenciaRepository->getActivesforTableByDateOnly($startDate, $endDate);
        } else {
            $entities = $emergenciaRepository->getActivesforTableByState($state, $startDate, $endDate);
        }

        return $this->render('emergencia/listado.html.twig', [
            'entities' => $entities,
            'currentState' => $state,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    #[Route('/{id}/expediente', name: 'app_emergencia_show_record', methods: ['GET'])]
    public function showRecord(Emergencia $emergencia, EntityManagerInterface $entityManager): Response
    {
        // Optional: Add a security check here to ensure the emergency is actually discharged
        if ($emergencia->getEstado() !== EmergenciasEstados::DISCHARGED) {
            $this->addFlash('error', 'Esta emergencia aun esta activa');
            return $this->redirectToRoute('app_emergencia_listado');
        }

        $evo = $entityManager->getRepository(EvolucionEmergencia::class)->findBy([
            'emergencia' => $emergencia,
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
        ], ['id' => 'DESC']);

        return $this->render('emergencia/show_record.html.twig', [
            'entity' => $emergencia,
            'evo' => $evo,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_emergencia_pdf', methods: ['GET'])]
    public function generatePdf(Emergencia $emergencia, EntityManagerInterface $entityManager): Response
    {
        // 1. Configure Dompdf Options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Helvetica');
        $pdfOptions->set('isRemoteEnabled', true); // Allows loading external images like a hospital logo

        // 2. Instantiate Dompdf
        $dompdf = new Dompdf($pdfOptions);

        // 3. Render the HTML using a dedicated PDF Twig template
        $html = $this->renderView('emergencia/pdf/expediente_pdf.html.twig', [
            'entity' => $emergencia,
            'mainConfig' => $entityManager->getRepository(MainConfiguration::class)->find(1),
        ]);

        // 4. Load the HTML into Dompdf
        $dompdf->loadHtml($html);

        // 5. Setup paper size (A4 is standard for medical docs)
        $dompdf->setPaper('A4', 'portrait');

        // 6. Render the PDF
        $dompdf->render();

        // 7. Stream the PDF to the browser
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Expediente_Emergencia_' . $emergencia->getId() . '.pdf"'
            ]
        );
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

        $evo = $entityManager->getRepository(EvolucionEmergencia::class)->findBy([
            'emergencia' => $emergencia,
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
        ], ['id' => 'DESC']);

        return $this->render('emergencia/pacienteShow.html.twig', [
            'entity' => $emergencia,
            'form' => $form->createView(),
            'evo' => $evo,
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
        if ($form->isSubmitted()) {

            $checkEmergency = $em->getRepository(Emergencia::class)->getEmergencyByPatient4Check($emergencia->getPaciente()->getId());
            if ($checkEmergency) {
                return $this->json([
                    'success' => false,
                    'message' => 'El paciente ya tiene una emergencia activa.'
                ]);
            }

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
                        'estado' => $emergencia->getEstado()->value,
                        'html' => $html
                    ])
                );
                $hub->publish($update);
            }

            return $this->json([
                'success' => true,
                'message' => 'Paciente vinculado exitosamente.',
                'paciente_nombre' => $emergencia->getPaciente()->getNombre() . ' ' . $emergencia->getPaciente()->getApellido(),
                'paciente_url' => $this->generateUrl('app_paciente_show', ['id' => $emergencia->getPaciente()->getId()]),
                'unlink_url' => $this->generateUrl('app_emergencia_unlink_patient', ['id' => $emergencia->getId()]),
            ]);
        }

        return $this->render('emergencia/forms/_associate_patient_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit-temporary-name', name: 'app_emergencia_edit_temporary_name', methods: ['GET', 'POST'])]
    public function editTempPatientName(Request $request, Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub): Response
    {
        $form = $this->createForm(EditTemporaryNameType::class, $emergencia, [
            'action' => $this->generateUrl('app_emergencia_edit_temporary_name', ['id' => $emergencia->getId()]),
        ]);

        $form->handleRequest($request);

        //@TODO: Fix csrf token for ajax forms
        if ($form->isSubmitted()) {

            $data = $form->getData();
            if (!$data->getPacienteTemporal()){
                return $this->json([
                    'success' => false,
                    'message' => 'El nombre temporal no puede esta vacio.'
                ]);
            }

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
                        'estado' => $emergencia->getEstado()->value,
                        'html' => $html
                    ])
                );
                $hub->publish($update);
            }

            return $this->json([
                'success' => true,
                'message' => 'Descripcion de paciente guardada exitosamente.',
                'paciente_nombre' => $emergencia->getPacienteTemporal(),
                'paciente_url' => $this->generateUrl('app_emergencia_edit_temporary_name', ['id' => $emergencia->getId()]),
            ]);
        }

        return $this->render('emergencia/forms/_edit_temporary_name.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/unlink-paciente', name: 'app_emergencia_unlink_patient', methods: ['POST'])]
    public function unlinkPatient(Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub): Response
    {
        // 1. Unlink the patient
        $emergencia->setPaciente(null);
        $em->flush();

        // 3. Mercure Broadcast (Updates the tables instantly!)
        $template = match($emergencia->getEstado()) {
            EmergenciasEstados::WAITING_TRIAGE => 'emergencia/tableRows/_new_er_table_row.html.twig',
            EmergenciasEstados::WAITING_BED => 'emergencia/tableRows/_new_triage_table.row.html.twig',
            EmergenciasEstados::IN_TREATMENT => 'emergencia/tableRows/_new_treatment_table_row.html.twig',
            default => null
        };

        if ($template) {
            $html = $this->renderView($template, ['emergencia' => $emergencia]);

            $update = new Update(
                'emergencias',
                json_encode([
                    'id' => $emergencia->getId(),
                    'estado' => strtolower($emergencia->getEstado()->value),
                    'html' => $html
                ])
            );
            $hub->publish($update);
        }

        // 4. Return the JSON payload for Stimulus
        return $this->json([
            'success' => true,
            'message' => 'Paciente desvinculado correctamente.',
            'paciente_nombre' => $emergencia->getPacienteTemporal(),
            'paciente_url' => $this->generateUrl('app_emergencia_edit_temporary_name', ['id' => $emergencia->getId()]),
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

            $sendToConsultation = $form->get('sendConsultation')->getData();
            $specialty = $form->get('specialty')->getData();

            if ($sendToConsultation) {
                $emergencia->setEstado(EmergenciasEstados::DERIVED_CONSULTATION);

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
                        'estado' => EmergenciasEstados::DERIVED_CONSULTATION->value,
                        'html' => $html
                    ])
                );
                $hub->publish($update);
                $this->addFlash('success', 'Paciente enviado a consulta externa de ' . $specialty);
            } else {
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
                        'estado' => EmergenciasEstados::WAITING_BED->value,
                        'html' => $html
                    ])
                );
                $hub->publish($update);
                $this->addFlash('success', 'Triaje registrado. Paciente en espera de cama.');
            }

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

    #[Route('/{id}/alta', name: 'app_emergencia_discharge', methods: ['GET', 'POST'])]
    public function dischargePatient(Request $request, Emergencia $emergencia, EntityManagerInterface $em, HubInterface $hub, AuditService $auditService): Response
    {
        $alta = new AltaMedica();
        // Automatically set the exit timestamp right now
        $alta->setFechaEgreso(new \DateTimeImmutable());

        $form = $this->createForm(AltaMedicaType::class, $alta, [
            'action' => $this->generateUrl('app_emergencia_discharge', ['id' => $emergencia->getId()]),
        ]);

        $form->handleRequest($request);

        //@TODO: Fix csrf token for ajax forms
        if ($form->isSubmitted()) {
            $data = $form->getData();

            if (!$emergencia->getPaciente()) {
                return $this->json([
                    'success' => false,
                    'message' => '¡Debe identificar y vincular al paciente (crear su perfil) antes de procesar el alta!'
                ]);
            }

            if (!$data->getDiagnosticoFinal()){
                return $this->json([
                    'success' => false,
                    'message' => 'El diagnostico no puede estar vacio.'
                ]);
            }

            // 2. Dynamic Validation & Sanitization
            switch ($alta->getCondicionAlta()) {
                case EmergenciasCondicionAlta::TRANSFER:
                    if (!$alta->getHospitalDestino() || !$alta->getMotivoTraslado()) {
                        return $this->json(['success' => false, 'message' => 'Debe especificar el hospital de destino y el motivo del traslado.']);
                    }
                    // Wipe irrelevant data
                    $alta->setServicioIngreso(null);
                    $alta->setIndicacionesMedicas(null);

                    $nombre = $emergencia->getPaciente()->getNombre();
                    $diagnose = $alta->getMotivoTraslado();
                    $auditService->persistAudit(
                        AuditTipos::EMERGENCY_DISCHARGE_TRANSFER,
                        "El Paciente $nombre debe ser trasladado a otro hospital debido a: $diagnose",
                        $emergencia->getPaciente(),
                        null,
                        null,
                        $emergencia
                    );

                    break;

                case EmergenciasCondicionAlta::ADMITTED_ROOM:
                    /*if (!$alta->getServicioIngreso()) {
                        return $this->json(['success' => false, 'message' => 'Debe seleccionar el servicio de hospitalización.']);
                    }*/
                    // Wipe irrelevant data
                    $alta->setHospitalDestino(null);
                    $alta->setMotivoTraslado(null);
                    $alta->setIndicacionesMedicas(null);

                    // --- THE NEW HANDOFF LOGIC ---
                    $hospitalizacion = new Hospitalizaciones();
                    $hospitalizacion->setPaciente($emergencia->getPaciente());
                    $hospitalizacion->setEmergencia($emergencia);
                    $hospitalizacion->setFechaIngreso(new \DateTime()); // Exact moment of ER discharge
                    $hospitalizacion->setDiagnosticoIngreso($alta->getDiagnosticoFinal());
                    $hospitalizacion->setEstado(HospitalizacionEstados::PENDING_BED);
                    $hospitalizacion->setVisitasPermitidas(true);

                    // You can use the 'motivoHospitalizacion' field to store the requested Service (e.g., UCI, Pediatria)
                    // so the admissions nurse knows which floor to send them to!
                    $hospitalizacion->setDiagnosticoIngreso('Servicio Solicitado: ' . $alta->getAreaHospitalizacion()->getNombre());

                    $em->persist($hospitalizacion);
                    // ------------------------------

                    $nombre = $emergencia->getPaciente()->getNombre();
                    $unit = $alta->getServicioIngreso();
                    $diagnose = $alta->getDiagnosticoFinal();
                    $auditService->persistAudit(
                        AuditTipos::EMERGENCY_DISCHARGE_ADMITTED_ROOM,
                        "El Paciente $nombre debe ser hospitalizado en $unit debido a: $diagnose",
                        $emergencia->getPaciente(),
                        null,
                        null,
                        $emergencia
                    );

                    break;

                case EmergenciasCondicionAlta::DECEASED:
                    // Wipe irrelevant data
                    $alta->setHospitalDestino(null);
                    $alta->setMotivoTraslado(null);
                    $alta->setServicioIngreso(null);
                    $alta->setIndicacionesMedicas(null);

                    $nombre = $emergencia->getPaciente()->getNombre();
                    $diagnose = $alta->getDiagnosticoFinal();
                    $auditService->persistAudit(
                        AuditTipos::EMERGENCY_DISCHARGE_DECEASED,
                        "El Paciente $nombre ha fallecido durante una emergencia. Diagnostico: $diagnose",
                        $emergencia->getPaciente(),
                        null,
                        null,
                        $emergencia
                    );

                    break;

                case EmergenciasCondicionAlta::SENT_HOME:
                    $nombre = $emergencia->getPaciente()->getNombre();
                    $diagnose = $alta->getDiagnosticoFinal();
                    $auditService->persistAudit(
                        AuditTipos::EMERGENCY_DISCHARGE_SENT_HOME,
                        "El Paciente $nombre fue dado de alta exitosamente. Diagnostico: $diagnose",
                        $emergencia->getPaciente(),
                        null,
                        null,
                        $emergencia
                    );
                    break;
                case EmergenciasCondicionAlta::LEFT:
                    $alta->setHospitalDestino(null);
                    $alta->setMotivoTraslado(null);
                    $alta->setServicioIngreso(null);

                    $nombre = $emergencia->getPaciente()->getNombre();
                    $diagnose = $alta->getDiagnosticoFinal();
                    $auditService->persistAudit(
                        AuditTipos::EMERGENCY_DISCHARGE_LEFT,
                        "El Paciente $nombre se ha retirado contra opinion medica. Diagnostico: $diagnose",
                        $emergencia->getPaciente(),
                        null,
                        null,
                        $emergencia
                    );

                    break;
            }

            // 3. Link the Alta to the Emergencia
            $emergencia->setAltaMedica($alta);
            $alta->setEmergencia($emergencia);

            // 4. State Transitions
            $emergencia->setEstado(EmergenciasEstados::DISCHARGED);

            // 5. FREE THE BED! (Crucial step)
            $needsCleaning = $form->has('needsCleaning') && $form->get('needsCleaning')->getData();

            if ($cama = $emergencia->getCamaActual()) {
                $cama->setEstado($needsCleaning ? CamaEstados::CLEANING : CamaEstados::AVAILABLE);
                $em->persist($cama);
                $emergencia->setCamaActual(null);
            }

            $em->persist($alta);
            $em->persist($emergencia);
            $em->flush();

            // 6. Mercure Push (Tells the tables to remove this row)
            $update = new Update(
                'emergencias',
                json_encode([
                    'id' => $emergencia->getId(),
                    'estado' => EmergenciasEstados::DISCHARGED->value,
                    'html' => ''
                ])
            );
            $hub->publish($update);

            return $this->json(['success' => true, 'message' => 'Paciente dado de alta exitosamente.']);
        }

        return $this->render('emergencia/forms/_alta_medica_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
