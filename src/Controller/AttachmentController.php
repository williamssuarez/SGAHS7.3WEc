<?php

namespace App\Controller;

use App\Entity\Attachments;
use App\Entity\Paciente;
use App\Exception\BusinessRuleException;
use App\Form\AttachmentType;
use App\Service\FileUploader;
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
    #[Route('pacientes/{id}/upload', name: 'app_attachment_paciente_upload', methods: ['GET', 'POST'])]
    public function pacienteUpload(Request $request, Paciente $paciente, FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {
        $attachment = new Attachments();
        $form = $this->createForm(AttachmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $name = $form->get('nombre')->getData();

            //process and upload the file
            try {
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
                $entityManager->flush();

                $this->addFlash('success', 'Archivo Subido.');
                return $this->redirectToRoute('app_paciente_show', ['id' => $paciente->getId()], Response::HTTP_SEE_OTHER);

            } catch (\RuntimeException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('attachment/paciente_upload.html.twig', [
            'paciente' => $paciente,
            'form' => $form,
        ]);
    }
}
