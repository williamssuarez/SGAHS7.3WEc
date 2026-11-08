<?php

namespace App\Controller;

use App\Entity\Attachments;
use App\Entity\Consulta;
use App\Entity\Paciente;
use App\Enum\AuditTipos;
use App\Exception\BusinessRuleException;
use App\Form\AttachmentType;
use App\Service\AuditService;
use App\Service\AttachmentUploader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/attachment')]
final class AttachmentController extends AbstractController
{
    #[Route('/pacientes/{id}/upload', name: 'app_attachment_paciente_upload', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_INTERNAL')]
    public function pacienteUpload(Request $request, Paciente $paciente, AttachmentUploader $attachmentUploader, EntityManagerInterface $entityManager, AuditService $auditService): Response
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

                $attachment->setPaciente($paciente);
                $attachment->setFiletype($file->getMimeType());
                $attachment->setDateUploaded(new \DateTime('now'));

                $fileHash = $attachmentUploader->upload($file);
                $attachment->setFilehash($fileHash);

                $entityManager->persist($attachment);

                $name = $paciente->getNombre();
                $attName = $attachment->getFilename();
                $fileType = $attachment->getFiletype();

                $auditService->persistAudit(
                    AuditTipos::PATIENT_FILE_NEW,
                    "Nuevo archivo $attName para $name de tipo: $fileType",
                    $paciente,
                    null
                );

                $entityManager->flush();

                $this->addFlash('success', 'Archivo Subido.');
                return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('attachment/paciente_upload.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }

    #[Route('/consulta/{id}/upload', name: 'app_attachment_consulta_upload', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_INTERNAL')]
    public function consultaUpload(Request $request, Consulta $consulta, AttachmentUploader $attachmentUploader, EntityManagerInterface $entityManager, AuditService $auditService): Response
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

                $attachment->setPaciente($consulta->getPaciente());
                $attachment->setFiletype($file->getMimeType());
                $attachment->setDateUploaded(new \DateTime('now'));

                $fileHash = $attachmentUploader->upload($file);
                $attachment->setFilehash($fileHash);

                $entityManager->persist($attachment);

                $name = $consulta->getPaciente()->getNombre();
                $attName = $attachment->getFilename();
                $fileType = $attachment->getFiletype();

                $auditService->persistAudit(
                    AuditTipos::CONSULT_FILE_NEW,
                    "Nuevo archivo $attName para $name de tipo: $fileType",
                    $consulta->getPaciente(),
                    $consulta
                );

                $entityManager->flush();

                $this->addFlash('success', 'Archivo Subido.');
                return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('attachment/consulta_upload.html.twig', [
            'consultum' => $consulta,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_attachment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_INTERNAL')]
    public function delete(Request $request, Attachments $attachments, EntityManagerInterface $entityManager, AttachmentUploader $attachmentUploader, AuditService $auditService): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $attachments->getId(), $submittedToken)) {
            $name = $attachments->getPaciente()->getNombre();
            $attName = $attachments->getFilename();
            $fileType = $attachments->getFiletype();

            $auditService->persistAudit(
                AuditTipos::PATIENT_FILE_DELETE,
                "Eliminacion de archivo $attName para $name de tipo: $fileType",
                $attachments->getPaciente(),
                null
            );
            $attachmentUploader->delete($attachments->getFilehash());
            $entityManager->remove($attachments);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }

    #[Route('/{id}/download', name: 'app_attachment_download', methods: ['GET'])]
    public function download(
        Attachments $attachment,
        AttachmentUploader $attachmentUploader,
        Security $security,
        Request $request
    ): Response {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Control de Acceso: Personal interno O el propio paciente titular vinculado
        $isInternal = $security->isGranted('ROLE_INTERNAL');
        $isOwner = false;
        if ($security->isGranted('ROLE_EXTERNAL')) {
            $patientLinked = $user?->getExternalProfile()?->getPaciente();
            if ($patientLinked && $patientLinked->getId() === $attachment->getPaciente()?->getId()) {
                $isOwner = true;
            }
        }

        if (!$isInternal && !$isOwner) {
            throw $this->createAccessDeniedException('No tiene privilegios para acceder a este documento médico.');
        }

        $filePath = $attachmentUploader->getFilePath($attachment->getFilehash());

        // Fallback para archivos previos a la migración que sigan en public/
        if (!file_exists($filePath)) {
            $legacyPath = $this->getParameter('kernel.project_dir') . '/public/uploads/pacientes/files/' . basename($attachment->getFilehash());
            if (file_exists($legacyPath)) {
                $filePath = $legacyPath;
            } else {
                throw $this->createNotFoundException('El archivo médico solicitado no se encuentra en el servidor.');
            }
        }

        $response = new BinaryFileResponse($filePath);

        $disposition = $request->query->getBoolean('download')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $response->setContentDisposition(
            $disposition,
            $attachment->getFilename()
        );
        if ($attachment->getFiletype()) {
            $response->headers->set('Content-Type', $attachment->getFiletype());
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
