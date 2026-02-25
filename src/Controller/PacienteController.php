<?php

namespace App\Controller;

use App\Entity\Paciente;
use App\Exception\BusinessRuleException;
use App\Form\PacienteType;
use App\Repository\HistoriaPacienteRepository;
use App\Repository\PacienteRepository;
use App\Repository\StatusRecordRepository;
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
    public function new(Request $request, PatientProcessor $patientProcessor): Response
    {
        $paciente = new Paciente();
        $form = $this->createForm(PacienteType::class, $paciente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // dejar que el servicio procese el form
                $paciente->setFallecido(0);
                $patientProcessor->processFormSubmission($paciente, $form->get('foto')->getData());

                $this->addFlash('success', 'Registro Agregado.');
                return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);

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
    public function show(Paciente $paciente, HistoriaPacienteRepository $historiaPacienteRepository, StatusRecordRepository $statusRecordRepository): Response
    {
        if ($paciente->getStatus() == $statusRecordRepository->getRemove()){
            $this->addFlash('danger', 'Registro no encontrado.');
            return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);
        }

        /*$historias = $historiaPacienteRepository->findByPacienteOrderedByDate($paciente->getId());

        $groupedHistorias = [];
        foreach ($historias as $historia) {
            // Get the date and format it as a string for grouping (e.g., "10 Feb. 2023")
            // Use IntlDateFormatter or a simple format string, like below:
            $dateString = $historia->getFechaAtendido()->format('j M. Y');

            // Initialize the array for this date if it doesn't exist
            if (!isset($groupedHistorias[$dateString])) {
                $groupedHistorias[$dateString] = [];
            }

            // Add the history record to the group
            $groupedHistorias[$dateString][] = $historia;
        }*/

        return $this->render('paciente/show.html.twig', [
            'paciente' => $paciente,
            //'grouped_historias' => $groupedHistorias
        ]);
    }

    #[Route('/{id}/edit', name: 'app_paciente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Paciente $paciente, EntityManagerInterface $entityManager, PatientProcessor $patientProcessor, StatusRecordRepository $statusRecordRepository): Response
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

                $this->addFlash('success', 'Registro Editado.');
                return $this->redirectToRoute('app_paciente_index', [], Response::HTTP_SEE_OTHER);

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
