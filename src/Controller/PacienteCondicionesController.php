<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\PacienteCondiciones;
use App\Enum\AuditTipos;
use App\Form\PacienteCondicionesType;
use App\Repository\PacienteCondicionesRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/paciente/condiciones')]
final class PacienteCondicionesController extends AbstractController
{
    #[Route(name: 'app_paciente_condiciones_index', methods: ['GET'])]
    public function index(PacienteCondicionesRepository $pacienteCondicionesRepository): Response
    {
        return $this->render('paciente_condiciones/index.html.twig', [
            'paciente_condiciones' => $pacienteCondicionesRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new/consulta', name: 'app_paciente_condiciones_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $pacienteCondicione = new PacienteCondiciones();
        $form = $this->createForm(PacienteCondicionesType::class, $pacienteCondicione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pacienteCondicione->setPaciente($consulta->getPaciente());
            $entityManager->persist($pacienteCondicione);

            $name = $consulta->getPaciente()->getNombre();
            $conditionName = $pacienteCondicione->getCondicion()->getNombre();

            $auditService->persistAudit(
                AuditTipos::CONSULT_CONDITION_NEW,
                "Nueva condicion $conditionName para $name",
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Condicion registrada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_condiciones/newConsulta.html.twig', [
            'paciente_condicione' => $pacienteCondicione,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_condiciones_show', methods: ['GET'])]
    public function show(PacienteCondiciones $pacienteCondicione): Response
    {
        return $this->render('paciente_condiciones/show.html.twig', [
            'paciente_condicione' => $pacienteCondicione,
        ]);
    }

    #[Route('/{id}/edit/{consulta}', name: 'app_paciente_condiciones_edit_consulta', methods: ['GET', 'POST'])]
    public function editConsulta(Request $request, PacienteCondiciones $pacienteCondicione, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $form = $this->createForm(PacienteCondicionesType::class, $pacienteCondicione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auditService->persistEditionAudit(
                $pacienteCondicione,
                AuditTipos::CONSULT_CONDITION_EDIT,
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Condicion editada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('paciente_condiciones/editConsulta.html.twig', [
            'paciente_condicione' => $pacienteCondicione,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_paciente_condiciones_delete', methods: ['POST'])]
    public function delete(Request $request, PacienteCondiciones $pacienteCondicione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pacienteCondicione->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pacienteCondicione);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_paciente_condiciones_index', [], Response::HTTP_SEE_OTHER);
    }
}
