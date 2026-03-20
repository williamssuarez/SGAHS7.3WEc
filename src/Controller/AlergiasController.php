<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\Consulta;
use App\Entity\ExternalProfile;
use App\Entity\Paciente;
use App\Entity\StatusRecord;
use App\Enum\AuditTipos;
use App\Form\AlergiasType;
use App\Repository\AlergiasRepository;
use App\Service\AuditService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/alergias')]
final class AlergiasController extends AbstractController
{
    #[Route(name: 'app_alergias_index', methods: ['GET'])]
    public function index(AlergiasRepository $alergiasRepository): Response
    {
        return $this->render('alergias/index.html.twig', [
            'alergias' => $alergiasRepository->findAll(),
        ]);
    }

    #[Route('/new/paciente/{id}', name: 'app_alergias_new_paciente', methods: ['GET', 'POST'])]
    public function newPaciente(Request $request, Paciente $paciente, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        if ($paciente->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'No se pudo encontrar la informacion');
            return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);
        }

        $alergia = new Alergias();
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $alergia->setPaciente($paciente);
            $entityManager->persist($alergia);

            $name = $paciente->getNombre();
            $allergyName = $alergia->getAlergeno()->getNombre();

            $auditService->persistAudit(
                AuditTipos::PATIENT_ALLERGY_NEW,
                "Nueva alergia $allergyName para $name",
                $paciente,
            );
            $entityManager->flush();

            $this->addFlash('success', 'Alergia registrada');
            return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/newPaciente.html.twig', [
            'alergia' => $alergia,
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/new/consulta/{id}', name: 'app_alergias_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, Consulta $consulta, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {

        $alergia = new Alergias();
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $alergia->setPaciente($consulta->getPaciente());
            $entityManager->persist($alergia);

            $name = $consulta->getPaciente()->getNombre();
            $allergyName = $alergia->getAlergeno()->getNombre();

            $auditService->persistAudit(
                AuditTipos::CONSULT_ALLERGY_NEW,
                "Nueva alergia $allergyName para $name",
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Alergia registrada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/newConsulta.html.twig', [
            'alergia' => $alergia,
            'consultum' => $consulta,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_show', methods: ['GET'])]
    public function show(Alergias $alergia): Response
    {
        return $this->render('alergias/show.html.twig', [
            'alergia' => $alergia,
        ]);
    }

    #[Route('/{id}/edit/paciente', name: 'app_alergias_edit_paciente', methods: ['GET', 'POST'])]
    public function editPaciente(Request $request, Alergias $alergia, EntityManagerInterface $entityManager, Paciente $paciente, AuditService $auditService): Response
    {
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $auditService->persistEditionAudit(
                $alergia,
                AuditTipos::PATIENT_ALLERGY_EDIT,
                $paciente,
                null
            );

            $entityManager->flush();

            $this->addFlash('success', 'Alergia Editada');
            return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/editPaciente.html.twig', [
            'alergia' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/consulta/{consulta}', name: 'app_alergias_edit_consulta', methods: ['GET', 'POST'])]
    public function editConsulta(Request $request, Alergias $alergia, EntityManagerInterface $entityManager, Consulta $consulta, AuditService $auditService): Response
    {
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $auditService->persistEditionAudit(
                $alergia,
                AuditTipos::CONSULT_ALLERGY_EDIT,
                $consulta->getPaciente(),
                $consulta
            );

            $entityManager->flush();

            $this->addFlash('success', 'Alergia Editada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/editConsulta.html.twig', [
            'alergia' => $alergia,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_delete', methods: ['POST'])]
    public function delete(Request $request, Alergias $alergia, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alergia->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alergia);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
    }
}
