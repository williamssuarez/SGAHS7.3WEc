<?php

namespace App\Controller;

use App\Entity\CitasConfiguraciones;
use App\Entity\CitasSolicitudes;
use App\Entity\MainConfiguration;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Enum\CitasEstados;
use App\Enum\CitasSolicitudesEstados;
use App\Form\CitasSolicitudesType;
use App\Repository\CitasSolicitudesRepository;
use App\Service\AppointmentScheduler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Citas;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/citas/solicitudes')]
final class CitasSolicitudesController extends AbstractController
{
    #[Route(name: 'app_citas_solicitudes_index', methods: ['GET'])]
    public function index(CitasSolicitudesRepository $citasSolicitudesRepository, EntityManagerInterface $entityManager): Response
    {
        $userEmail = $this->getUser()->getUserIdentifier();
        $userObj = $entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail, 'status' => $entityManager->getRepository(StatusRecord::class)->getActive()]);
        $paciente = $userObj->getExternalProfile()->getPaciente();

        return $this->render('citas_solicitudes/index.html.twig', [
            'entities' => $citasSolicitudesRepository->getActivesforTableByPaciente($paciente->getId()),
        ]);
    }

    #[Route('/new', name: 'app_citas_solicitudes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $citasSolicitude = new CitasSolicitudes();
        $form = $this->createForm(CitasSolicitudesType::class, $citasSolicitude);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userEmail = $this->getUser()->getUserIdentifier();
            $userObj = $entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
            $paciente = $userObj->getExternalProfile()->getPaciente();

            $citasSolicitude->setPaciente($paciente);
            $entityManager->persist($citasSolicitude);
            $entityManager->flush();

            $this->addFlash('success', 'La solicitud de cita ha sido ingresada. Espere mientras es asignado una fecha.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/new.html.twig', [
            'entity' => $citasSolicitude,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}', name: 'app_citas_solicitudes_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['uuid' => 'uuid'])] CitasSolicitudes $citasSolicitude, EntityManagerInterface $entityManager): Response
    {
        if ($citasSolicitude->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_NOT_FOUND);
        }

        $cita = null;
        if ($citasSolicitude->getEstadoSolicitud() == CitasSolicitudesEstados::SCHEDULED){
            $cita = $entityManager->getRepository(Citas::class)->findOneBy([
                'solicitud' => $citasSolicitude,
                'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
            ]);
        }
        return $this->render('citas_solicitudes/show.html.twig', [
            'entity' => $citasSolicitude,
            'cita' => $cita,
        ]);
    }

    #[Route('/{uuid}/edit', name: 'app_citas_solicitudes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['uuid' => 'uuid'])] CitasSolicitudes $citasSolicitudes, EntityManagerInterface $entityManager): Response
    {
        if ($citasSolicitudes->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CitasSolicitudesType::class, $citasSolicitudes);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La solicitud de cita ha sido editada. Espere mientras es asignado una fecha.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_solicitudes/edit.html.twig', [
            'entity' => $citasSolicitudes,
            'form' => $form,
        ]);
    }

    #[Route('/{uuid}/cancel', name: 'app_citas_solicitudes_cancelar', methods: ['POST'])]
    public function cancel(#[MapEntity(mapping: ['uuid' => 'uuid'])] CitasSolicitudes $citasSolicitudes, EntityManagerInterface $em): Response
    {
        if ($citasSolicitudes->getStatus() != $em->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_NOT_FOUND);
        }

        $citasSolicitudes->setEstadoSolicitud(CitasSolicitudesEstados::CANCELED);
        $em->persist($citasSolicitudes);

        $em->flush();

        $this->addFlash('success', 'La solicitud de cita ha sido cancelada.');
        return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{uuid}/voucher', name: 'app_citas_solicitudes_voucher', methods: ['GET'])]
    public function downloadVoucher(#[MapEntity(mapping: ['uuid' => 'uuid'])] CitasSolicitudes $citasSolicitudes, UrlGeneratorInterface $router, EntityManagerInterface $entityManager): Response
    {
        if ($citasSolicitudes->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($citasSolicitudes->getEstadoSolicitud() != CitasSolicitudesEstados::SCHEDULED){
            $this->addFlash('error', 'La solicitud de cita ya no esta programada.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_FORBIDDEN);
        }

        $cita = $entityManager->getRepository(Citas::class)->findOneBy([
            'solicitud' => $citasSolicitudes,
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
            'estadoCita' => CitasEstados::EXPECTED
        ]);

        if (!$cita){
            $this->addFlash('error', 'La solicitud no pudo ser encontrada, intente mas tarde.');
            return $this->redirectToRoute('app_citas_solicitudes_index', [], Response::HTTP_NOT_FOUND);
        }

        // 1. Generate the absolute URL for the verification page
        $verificationUrl = $router->generate('app_public_verifications_verify_cita', [
            'uuid' => $cita->getUuid()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $mainConfig = $entityManager->getRepository(MainConfiguration::class)->findOneBy([
            'id' => 1
        ]);

        // 2. Render the Twig template (which will include the QR code)
        $html = $this->renderView('citas_solicitudes/voucher_pdf.html.twig', [
            'cita' => $cita,
            'verificationUrl' => $verificationUrl,
            'mainConfig' => $mainConfig
        ]);

        // 3. Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // Needed if loading external CSS/images

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait'); // A5 is a nice size for a voucher
        $dompdf->render();

        // 4. Output the PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
}
