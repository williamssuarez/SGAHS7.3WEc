<?php

namespace App\Controller;

use App\Entity\Attachments;
use App\Entity\Consulta;
use App\Entity\Paciente;
use App\Enum\AuditTipos;
use App\Exception\BusinessRuleException;
use App\Form\AttachmentType;
use App\Service\AuditService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/attachment')]
final class AttachmentController extends AbstractController
{
    #[Route('/pacientes/{id}/upload', name: 'app_attachment_paciente_upload', methods: ['GET', 'POST'])]
    public function pacienteUpload(Request $request, Paciente $paciente, FileUploader $fileUploader, EntityManagerInterface $entityManager, AuditService $auditService): Response
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

                $fileHash = $fileUploader->upload($file);
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
    public function consultaUpload(Request $request, Consulta $consulta, FileUploader $fileUploader, EntityManagerInterface $entityManager, AuditService $auditService): Response
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

                $fileHash = $fileUploader->upload($file);
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
    public function delete(Request $request, Attachments $attachments, EntityManagerInterface $entityManager, FileUploader $fileUploader, AuditService $auditService): Response
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
            $fileUploader->delete($attachments->getFilehash());
            $entityManager->remove($attachments);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
