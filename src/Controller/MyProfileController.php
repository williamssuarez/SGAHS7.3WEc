<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\Attachments;
use App\Entity\Audit;
use App\Entity\Consulta;
use App\Entity\Enfermedades;
use App\Entity\ExternalProfile;
use App\Entity\PacienteCondiciones;
use App\Entity\PacienteDiscapacidades;
use App\Entity\PacienteEnfermedades;
use App\Entity\PacienteInmunizaciones;
use App\Entity\Prescripciones;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Entity\Vitales;
use App\Enum\AuditTipos;
use App\Enum\PrescripcionesEstados;
use App\Exception\BusinessRuleException;
use App\Form\AlergiasType;
use App\Form\AttachmentType;
use App\Form\UserType;
use App\Repository\StatusRecordRepository;
use App\Repository\UserRepository;
use App\Service\AuditService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/my_profile')]
final class MyProfileController extends AbstractController
{
    #[Route(name: 'app_user_external_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('my_profile/index.html.twig', [
            'users' => $userRepository->getActivesExternalsforTable(),
        ]);
    }

    #[Route('/{uuid}', name: 'my_profile_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['uuid' => 'uuid'])] ExternalProfile $externalProfile, EntityManagerInterface $entityManager): Response
    {
        $paciente = $externalProfile->getPaciente();

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
        /*$allHistory = $entityManager->getRepository(Audit::class)->findBy([
            'paciente' => $paciente->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ], ['id' => 'DESC']);*/

        return $this->render('my_profile/show.html.twig', [
            'paciente' => $paciente,
            'vitales' => $vitales,
            'prescripcionesActivas' => $prescripcionesActivas,
            'prescripcionesInactivas' => $prescripcionesInactivas,
            'alergias' => $alergias,
            'condiciones' => $condiciones,
            'enfermedades' => $enfermedades,
            'discapacidades' => $discapacidades,
            'inmunizaciones' => $inmunizaciones,
            //'allHistory' => $allHistory,
        ]);
    }

    #[Route('/new/alergia/{uuid}', name: 'my_profile_alergias_new', methods: ['GET', 'POST'])]
    public function newAlergias(#[MapEntity(mapping: ['uuid' => 'uuid'])] ExternalProfile $externalProfile, Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {

        $paciente = $externalProfile->getPaciente();
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
                "El Paciente $name agrego una nueva alergia $allergyName para si mismo(a)",
                $paciente,
            );
            $entityManager->flush();

            $this->addFlash('success', 'Alergia registrada');
            return $this->redirectToRoute('my_profile_show', ['uuid' => $externalProfile->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/newExtUser.html.twig', [
            'alergia' => $alergia,
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/alergia/{uuid}', name: 'my_profile_alergias_edit', methods: ['GET', 'POST'])]
    public function editAlergias(Alergias $alergia, #[MapEntity(mapping: ['uuid' => 'uuid'])] ExternalProfile $externalProfile, Request $request, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $paciente = $externalProfile->getPaciente();
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
            return $this->redirectToRoute('my_profile_show', ['uuid' => $externalProfile->getUuid()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/editExtUser.html.twig', [
            'alergia' => $alergia,
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/new/{uuid}/upload', name: 'my_profile_attachment_upload', methods: ['GET', 'POST'])]
    public function myProfileUpload(Request $request, #[MapEntity(mapping: ['uuid' => 'uuid'])] ExternalProfile $externalProfile, FileUploader $fileUploader, EntityManagerInterface $entityManager, AuditService $auditService): Response
    {
        $attachment = new Attachments();
        $form = $this->createForm(AttachmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $name = $form->get('nombre')->getData();

            //process and upload the file
            try {

                if (!$file) {
                    throw new BusinessRuleException('Debe subir un archivo.');
                }

                if ($name){
                    $attachment->setFilename($name);
                } else {
                    $attachment->setFilename($file->getClientOriginalName());
                }

                $attachment->setPaciente($externalProfile->getPaciente());
                $attachment->setFiletype($file->getMimeType());
                $attachment->setDateUploaded(new \DateTime('now'));
                $attachment->setUuid($externalProfile->getUuid());

                $fileHash = $fileUploader->upload($file);
                $attachment->setFilehash($fileHash);

                $entityManager->persist($attachment);

                $name = $externalProfile->getPaciente()->getNombre();
                $attName = $attachment->getFilename();
                $fileType = $attachment->getFiletype();

                $auditService->persistAudit(
                    AuditTipos::CONSULT_FILE_NEW,
                    "El paciente $name agrego un nuevo archivo $attName de tipo: $fileType",
                    $externalProfile->getPaciente(),
                    null
                );

                $entityManager->flush();

                $this->addFlash('success', 'Archivo Subido.');
                return $this->redirectToRoute('my_profile_show', ['uuid' => $externalProfile->getUuid()], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('attachment/my_profile_upload.html.twig', [
            'form' => $form,
            'paciente' => $externalProfile->getPaciente(),
        ]);
    }

    #[Route('/delete/{id}/upload/{uuid}', name: 'my_profile_attachment_delete_upload', methods: ['POST'])]
    public function myProfileDeleteUpload(Request $request, Attachments $attachments, #[MapEntity(mapping: ['uuid' => 'uuid'])] ExternalProfile $externalProfile, EntityManagerInterface $entityManager, FileUploader $fileUploader, AuditService $auditService): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $attachments->getId(), $submittedToken)) {
            $name = $attachments->getPaciente()->getNombre();
            $attName = $attachments->getFilename();
            $fileType = $attachments->getFiletype();

            $auditService->persistAudit(
                AuditTipos::PATIENT_FILE_DELETE,
                "El paciente $name elimino archivo $attName de tipo: $fileType",
                $attachments->getPaciente(),
                null
            );
            $fileUploader->delete($attachments->getFilehash());
            $entityManager->remove($attachments);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
