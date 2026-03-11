<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\Audit;
use App\Entity\Consulta;
use App\Entity\HistoriaPaciente;
use App\Entity\Paciente;
use App\Entity\PacienteCondiciones;
use App\Entity\PacienteDiscapacidades;
use App\Entity\PacienteEnfermedades;
use App\Entity\PacienteInmunizaciones;
use App\Entity\Prescripciones;
use App\Entity\StatusRecord;
use App\Entity\Vitales;
use App\Enum\AuditTipos;
use App\Enum\PrescripcionesEstados;
use App\Exception\BusinessRuleException;
use App\Form\PacienteType;
use App\Repository\HistoriaPacienteRepository;
use App\Repository\PacienteRepository;
use App\Repository\StatusRecordRepository;
use App\Service\AuditService;
use App\Service\FileUploader;
use App\Service\PatientProcessor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/paciente')]
final class PacienteController extends AbstractController
{
    #[Route('/autocomplete-paciente', name: 'app_paciente_autocomplete', methods: ['GET'])]
    public function search(Request $request, PacienteRepository $repository): JsonResponse
    {
        $query = $request->query->get('q'); // Select2 sends the search term as 'q'
        $pacientes = $repository->findByNombreLike($query);

        $results = [];
        foreach ($pacientes as $p) {
            $results[] = [
                'id' => $p->getId(),
                'text' => sprintf('%s %s (%s-%s)', $p->getNombre(), $p->getApellido(), $p->getTipoDocumento(), number_format($p->getCedula(), 0, ',', '.'))
            ];
        }

        return new JsonResponse(['results' => $results]);
    }

    #[Route(name: 'app_paciente_index', methods: ['GET'])]
    public function index(PacienteRepository $pacienteRepository): Response
    {
        return $this->render('paciente/index.html.twig', [
            'pacientes' => $pacienteRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_paciente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PatientProcessor $patientProcessor, AuditService $auditService): Response
    {
        $paciente = new Paciente();
        $form = $this->createForm(PacienteType::class, $paciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // dejar que el servicio procese el form
                $paciente->setFallecido(0);
                $patientProcessor->processFormSubmission($paciente, $form->get('foto')->getData());

                $name = $paciente->getNombre();
                $auditService->persistAndFlushAudit(
                    AuditTipos::PATIENT_NEW,
                    "Nueva registro de paciente: $name",
                    $paciente,
                    null
                );

                $this->addFlash('success', 'Registro Agregado.');
                return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('paciente/new.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_show', methods: ['GET'])]
    public function show(Paciente $paciente, HistoriaPacienteRepository $historiaPacienteRepository, StatusRecordRepository $statusRecordRepository, EntityManagerInterface $entityManager): Response
    {
        if ($paciente->getStatus() == $statusRecordRepository->getRemove()){
            $this->addFlash('danger', 'Registro no encontrado.');
            return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        //vitales
        $vitales = $entityManager->getRepository(Vitales::class)->getActivesforTable($paciente->getId());

        //prescripciones
        $prescripcionesActivas = $entityManager->getRepository(Prescripciones::class)->getActivesforTableByState($paciente->getId(), PrescripcionesEstados::ACTIVE);
        $prescripcionesInactivas = $entityManager->getRepository(Prescripciones::class)->getActivesforTableByNotState($paciente->getId(), PrescripcionesEstados::ACTIVE);

        //alergias
        $alergias = $entityManager->getRepository(Alergias::class)->getActivesforTable($paciente->getId());

        //condiciones
        $condiciones = $entityManager->getRepository(PacienteCondiciones::class)->getActivesforTable($paciente->getId());

        //enfermedades
        $enfermedades = $entityManager->getRepository(PacienteEnfermedades::class)->getActivesforTable($paciente->getId());

        //discapacidades
        $discapacidades = $entityManager->getRepository(PacienteDiscapacidades::class)->getActivesforTable($paciente->getId());

        //inmunizaciones
        $inmunizaciones = $entityManager->getRepository(PacienteInmunizaciones::class)->getActivesforTable($paciente->getId());

        //historial completo
        $allHistory = $entityManager->getRepository(Audit::class)->findBy([
            'paciente' => $paciente->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ], ['id' => 'DESC']);

        return $this->render('paciente/show.html.twig', [
            'paciente' => $paciente,
            'vitales' => $vitales,
            'prescripcionesActivas' => $prescripcionesActivas,
            'prescripcionesInactivas' => $prescripcionesInactivas,
            'alergias' => $alergias,
            'condiciones' => $condiciones,
            'enfermedades' => $enfermedades,
            'discapacidades' => $discapacidades,
            'inmunizaciones' => $inmunizaciones,
            'allHistory' => $allHistory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paciente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Paciente $paciente, AuditService $auditService, PatientProcessor $patientProcessor, StatusRecordRepository $statusRecordRepository): Response
    {
        if ($paciente->getStatus() == $statusRecordRepository->getRemove()){
            $this->addFlash('danger', 'Registro no encontrado.');
            return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PacienteType::class, $paciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // dejar que el servicio procese el form

                $patientProcessor->processFormSubmission($paciente, $form->get('foto')->getData());

                $auditService->persistEditionAndFlushAudit(
                    $paciente,
                    AuditTipos::PATIENT_EDIT,
                    $paciente,
                    null
                );

                $this->addFlash('success', 'Registro Editado.');
                return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('paciente/edit.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_delete', methods: ['POST'])]
    public function delete(Request $request, Paciente $paciente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$paciente->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($paciente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
    }
}
