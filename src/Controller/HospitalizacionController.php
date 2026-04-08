<?php

namespace App\Controller;

use App\Entity\Emergencia;
use App\Entity\EvolucionHospitalaria;
use App\Entity\Hospitalizaciones;
use App\Entity\IndicacionMedica;
use App\Entity\KardexEnfermeria;
use App\Entity\SignosVitalesHospitalarios;
use App\Entity\VisitaHospitalaria;
use App\Enum\CamaEstados;
use App\Enum\EmergenciasEstados;
use App\Enum\HospitalizacionEstados;
use App\Enum\IndicacionMedicaEstado;
use App\Form\AltaHospitalariaType;
use App\Form\AsignarCamaHospitalizacionType;
use App\Form\AsignarCamaType;
use App\Form\EvolucionHospitalariaType;
use App\Form\IndicacionMedicaType;
use App\Form\SignosVitalesType;
use App\Repository\HospitalizacionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/hospitalizacion')]
class HospitalizacionController extends AbstractController
{
    #[Route('/admisiones', name: 'app_hospitalizacion_admisiones', methods: ['GET'])]
    public function admisiones(HospitalizacionesRepository $hospitalizacionRepository): Response
    {
        // Require the new nurse/admin roles we discussed!
        $this->denyAccessUnlessGranted('ROLE_NURSE');

        $pendientes = $hospitalizacionRepository->getActivesforTableByState(HospitalizacionEstados::PENDING_BED);

        return $this->render('hospitalizaciones/admisiones.html.twig', [
            'pendientes' => $pendientes,
        ]);
    }

    #[Route('/censo', name: 'app_hospitalizacion_censo', methods: ['GET'])]
    public function censo(HospitalizacionesRepository $hospitalizacionRepository): Response
    {
        // Require the doctor/nurse roles
        $this->denyAccessUnlessGranted('ROLE_INTERNAL');

        $activeAdmissions = $hospitalizacionRepository->findActiveCensus();

        // Group the patients by Area for the UI
        $groupedCenso = [];
        foreach ($activeAdmissions as $hospitalizacion) {
            $areaName = $hospitalizacion->getCamaActual()->getHabitacion()->getArea()->getNombre();
            $groupedCenso[$areaName][] = $hospitalizacion;
        }

        return $this->render('hospitalizaciones/censo.html.twig', [
            'groupedCenso' => $groupedCenso,
        ]);
    }

    #[Route('/{id}/expediente', name: 'app_hospitalizacion_expediente', methods: ['GET'])]
    public function expediente(Hospitalizaciones $hospitalizacion): Response
    {
        $this->denyAccessUnlessGranted('ROLE_INTERNAL');

        // Security check: Make sure they are actually admitted (or discharged, if viewing history)
        if ($hospitalizacion->getEstado() === HospitalizacionEstados::PENDING_BED->value) {
            $this->addFlash('warning', 'Este paciente aún no tiene cama asignada.');
            return $this->redirectToRoute('app_hospitalizacion_admisiones');
        }

        $evolucion = new EvolucionHospitalaria();
        $evolucionForm = $this->createForm(EvolucionHospitalariaType::class, $evolucion);

        $indicacion = new IndicacionMedica();
        $indicacionForm = $this->createForm(IndicacionMedicaType::class, $indicacion);

        // Inside your expediente() method in HospitalizacionController:
        $vitalsHistory = $hospitalizacion->getSignosVitalesHospitalarios()->toArray();

        // Sort by date ASC for the chart
        usort($vitalsHistory, fn($a, $b) => $a->getCreated() <=> $b->getCreated());

        $chartLabels = [];
        $chartTemp = [];
        $chartFC = [];

        foreach ($vitalsHistory as $vital) {
            $chartLabels[] = $vital->getCreated()->format('d/m H:i');
            $chartTemp[] = $vital->getTemperatura();
            $chartFC[] = $vital->getFrecuenciaCardiaca();
        }

        $vitalsChartData = [
            'labels' => $chartLabels,
            'temp' => $chartTemp,
            'fc' => $chartFC,
        ];

        $vitales = new SignosVitalesHospitalarios();
        $vitalsForm = $this->createForm(SignosVitalesType::class, $vitales);

        $altaForm = $this->createForm(AltaHospitalariaType::class, $hospitalizacion, [
            'action' => $this->generateUrl('app_hospitalizacion_dar_alta', ['id' => $hospitalizacion->getId()])
        ]);

        return $this->render('hospitalizaciones/expediente.html.twig', [
            'entity' => $hospitalizacion,
            'evolucionForm' => $evolucionForm->createView(),
            'indicacionForm' => $indicacionForm->createView(),
            'vitalsChartData' => $vitalsChartData,
            'vitalsForm' => $vitalsForm->createView(),
            'altaForm' => $altaForm->createView()
        ]);
    }

    #[Route('/{id}/dar-alta', name: 'app_hospitalizacion_dar_alta', methods: ['POST'])]
    public function darAlta(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DOCTOR');

        // Prevent discharging someone twice
        if ($hospitalizacion->getEstado() === HospitalizacionEstados::DISCHARGED) {
            $this->addFlash('warning', 'Este paciente ya fue dado de alta.');
            return $this->redirectToRoute('app_hospitalizacion_censo');
        }

        $form = $this->createForm(AltaHospitalariaType::class, $hospitalizacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Update Hospitalization metadata
            $hospitalizacion->setEstado(HospitalizacionEstados::DISCHARGED);
            $hospitalizacion->setFechaEgreso(new \DateTime());

            // If you used the unmapped field for instructions, you'd save it here,
            // perhaps appending it to the diagnostic text or saving to a specific column.
            $indicaciones = $form->get('indicacionesAlta')->getData();
            if ($indicaciones) {
                $hospitalizacion->setDiagnosticoEgreso(
                    $hospitalizacion->getDiagnosticoEgreso() . "\n\nINDICACIONES AL ALTA:\n" . $indicaciones
                );
            }

            // 2. FREE THE BED
            $cama = $hospitalizacion->getCamaActual();
            if ($cama) {
                // Ideally, a bed goes to 'CLEANING' so housekeeping knows to change the sheets,
                // but if you don't have that state, set it directly to 'AVAILABLE'.
                $cama->setEstado(CamaEstados::CLEANING);
                $em->persist($cama);
            }

            // Note: We DO NOT set $hospitalizacion->setCamaActual(null).
            // We keep the relation intact so historical records show exactly which bed they were in!

            $em->flush();

            $this->addFlash('success', 'El paciente ha sido dado de alta. La cama ha sido liberada.');
            return $this->redirectToRoute('app_hospitalizacion_censo');
        }

        $this->addFlash('danger', 'Ocurrió un error procesando el alta.');
        return $this->redirectToRoute('app_hospitalizacion_expediente', ['id' => $hospitalizacion->getId()]);
    }

    #[Route('/{id}/nueva-evolucion', name: 'app_hospitalizacion_nueva_evolucion', methods: ['POST'])]
    public function nuevaEvolucion(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DOCTOR'); // Only doctors can write these!

        $evolucion = new EvolucionHospitalaria();
        $form = $this->createForm(EvolucionHospitalariaType::class, $evolucion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // SECURE THE METADATA
            $evolucion->setHospitalizacion($hospitalizacion);

            $em->persist($evolucion);
            $em->flush();

            $this->addFlash('success', 'Evolución clínica guardada y firmada exitosamente.');
        } else {
            $this->addFlash('danger', 'Hubo un error al guardar la evolución.');
        }

        return $this->redirectToRoute('app_hospitalizacion_expediente', ['id' => $hospitalizacion->getId()]);
    }

    #[Route('/{id}/nueva-indicacion', name: 'app_hospitalizacion_nueva_indicacion', methods: ['POST'])]
    public function nuevaIndicacion(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DOCTOR');

        $indicacion = new IndicacionMedica();
        $form = $this->createForm(IndicacionMedicaType::class, $indicacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // SECURE THE METADATA
            $indicacion->setHospitalizacion($hospitalizacion);
            $indicacion->setEstado(IndicacionMedicaEstado::ACTIVE);

            $em->persist($indicacion);
            $em->flush();

            $this->addFlash('success', 'Indicación médica agregada al Kardex.');
        } else {
            $this->addFlash('danger', 'Error al procesar la indicación.');
        }

        return $this->redirectToRoute('app_hospitalizacion_expediente', ['id' => $hospitalizacion->getId()]);
    }

    #[Route('/indicacion/{id}/suspender', name: 'app_hospitalizacion_suspender_indicacion', methods: ['POST'])]
    public function suspenderIndicacion(Request $request, IndicacionMedica $indicacion, EntityManagerInterface $em): Response
    {
        // 1. Security Check: Only doctors can suspend orders
        $this->denyAccessUnlessGranted('ROLE_DOCTOR');

        // We need the parent hospitalization to know where to redirect back to
        $hospitalizacion = $indicacion->getHospitalizacion();

        // 2. Validate the CSRF token passed from the form
        if ($this->isCsrfTokenValid('suspend' . $indicacion->getId(), $request->request->get('_token'))) {

            // 3. Prevent double-suspensions
            if ($indicacion->getEstado() === 'SUSPENDIDA') {
                $this->addFlash('warning', 'Esta indicación ya se encontraba suspendida.');
            } else {
                // 4. Update the state
                $indicacion->setEstado(IndicacionMedicaEstado::SUSPENDED);

                // (Optional) If you want to use your AuditService here, this is the perfect place!
                // $auditService->persistAudit(AuditTipos::MEDICAL_ORDER_SUSPENDED, ...);

                $em->flush();
                $this->addFlash('success', 'La indicación fue suspendida. Ya no aparecerá en el Kardex activo de enfermería.');
            }
        } else {
            $this->addFlash('danger', 'Token de seguridad inválido.');
        }

        // 5. Redirect back to the patient's record
        return $this->redirectToRoute('app_hospitalizacion_expediente', [
            'id' => $hospitalizacion->getId()
        ]);
    }

    #[Route('/indicacion/{id}/administrar', name: 'app_hospitalizacion_administrar_indicacion', methods: ['POST'])]
    public function administrarIndicacion(Request $request, IndicacionMedica $indicacion, EntityManagerInterface $em): Response
    {
        // 1. Security Check: Only nurses (and maybe doctors) can administer
        $this->denyAccessUnlessGranted('ROLE_NURSE');

        $hospitalizacion = $indicacion->getHospitalizacion();

        // 2. Validate the CSRF token
        if ($this->isCsrfTokenValid('administer' . $indicacion->getId(), $request->request->get('_token'))) {

            if ($indicacion->getEstado() !== IndicacionMedicaEstado::ACTIVE) {
                $this->addFlash('warning', 'No se puede administrar una indicación suspendida.');
            } else {
                // 3. Create the Execution Record
                $kardex = new KardexEnfermeria();
                $kardex->setIndicacionMedica($indicacion);
                $kardex->setEstado('Administrado');

                // (Optional) If they passed an observation via a modal prompt
                $obs = $request->request->get('observacion');
                if ($obs) {
                    $kardex->setObservacion($obs);
                }

                $em->persist($kardex);
                $em->flush();

                $this->addFlash('success', 'Administración registrada exitosamente.');
            }
        } else {
            $this->addFlash('danger', 'Token de seguridad inválido.');
        }

        return $this->redirectToRoute('app_hospitalizacion_expediente', [
            'id' => $hospitalizacion->getId()
        ]);
    }

    #[Route('/{id}/registrar-signos', name: 'app_hospitalizacion_registrar_signos', methods: ['POST'])]
    public function registrarSignos(Request $request, Hospitalizaciones $hospitalizacion, EntityManagerInterface $em): Response
    {
        // Both nurses and doctors can take vitals
        $this->denyAccessUnlessGranted('ROLE_INTERNAL');

        $signos = new SignosVitalesHospitalarios();
        $form = $this->createForm(SignosVitalesType::class, $signos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Secure the metadata!
            $signos->setHospitalizacion($hospitalizacion);

            $em->persist($signos);
            $em->flush();

            $this->addFlash('success', 'Signos vitales registrados exitosamente.');
        } else {
            $this->addFlash('danger', 'Error al registrar los signos vitales. Verifique los datos.');
        }

        return $this->redirectToRoute('app_hospitalizacion_expediente', ['id' => $hospitalizacion->getId()]);
    }

    #[Route('/{id}/asignar-cama', name: 'app_hospitalizacion_assign_bed', methods: ['GET', 'POST'])]
    public function assignBed(Request $request, Hospitalizaciones $hospitalizaciones, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($hospitalizaciones->getEstado() !== HospitalizacionEstados::PENDING_BED) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'error' => 'Estado inválido para asignar cama.']);
            }
            $this->addFlash('danger', 'Estado inválido para asignar cama.');
            return $this->redirectToRoute('app_hospitalizacion_admisiones');
        }

        $form = $this->createForm(AsignarCamaHospitalizacionType::class, $hospitalizaciones, [
            'action' => $this->generateUrl('app_hospitalizacion_assign_bed', ['id' => $hospitalizaciones->getId()]),
        ]);

        $form->handleRequest($request);

        // @TODO: Fix AJAX CSRF
        if ($form->isSubmitted()) {
            $cama = $hospitalizaciones->getCamaActual();

            if ($cama->getEstado() !== CamaEstados::AVAILABLE) {
                return $this->json(['success' => false, 'error' => 'Esta cama acaba de ser ocupada.']);
            }

            $hospitalizaciones->setEstado(HospitalizacionEstados::ADMITTED);
            $cama->setEstado(CamaEstados::OCUPIED);

            $em->persist($hospitalizaciones);
            $em->persist($cama);
            $em->flush();

            return $this->json(['success' => true, 'message' => 'Cama asignada exitosamente.']);
        }

        // THE FIX: Manually generate the token using the exact name the form component uses
        $tokenId = $form->getName(); // Usually 'asignar_cama'
        $tokenValue = $csrfTokenManager->getToken($tokenId)->getValue();

        // Pass the raw string directly to Twig
        return $this->render('hospitalizaciones/_assign_bed_form.html.twig', [
            'form' => $form->createView(),
            'manual_csrf_token' => $tokenValue, // <--- Passing it here
        ]);
    }
}
