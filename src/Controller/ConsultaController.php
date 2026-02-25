<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Enum\ConsultaEstados;
use App\Exception\BusinessRuleException;
use App\Form\ConsultaActiveType;
use App\Form\ConsultaCancelType;
use App\Form\ConsultaPendingType;
use App\Repository\ConsultaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consulta')]
final class ConsultaController extends AbstractController
{
    #[Route(name: 'app_consulta_index', methods: ['GET'])]
    public function index(ConsultaRepository $consultaRepository): Response
    {
        return $this->render('consulta/index.html.twig', [
            'entities' => $consultaRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new-pending', name: 'app_consulta_new_pending', methods: ['GET', 'POST'])]
    public function newPending(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultum = new Consulta();
        $form = $this->createForm(ConsultaPendingType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $consultum->setEstadoConsulta(ConsultaEstados::PENDING);
            $entityManager->persist($consultum);
            $entityManager->flush();

            $this->addFlash('success', 'Consulta Programada.');
            return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/new.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
            'consultaLabel' => 'Nueva Consulta Pendiente'
        ]);
    }

    #[Route('/new-active', name: 'app_consulta_new_active', methods: ['GET', 'POST'])]
    public function newActive(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultum = new Consulta();
        $form = $this->createForm(ConsultaActiveType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));

            try {
                if ($consultum->getFechaInicio() <= $currentTime ){
                    throw new BusinessRuleException('La fecha de inicio de la consulta no puede ser antes de la fecha actual, por favor verifique.');
                }

                $consultum->setEstadoConsulta(ConsultaEstados::ACTIVE);
                $entityManager->persist($consultum);
                $entityManager->flush();

                $this->addFlash('success', 'Consulta Agregada e Iniciada.');
                return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
            } catch (BusinessRuleException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('consulta/new.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
            'consultaLabel' => 'Nueva Consulta Activa'
        ]);
    }

    #[Route('/{id}', name: 'app_consulta_show', methods: ['GET'])]
    public function show(Consulta $consultum): Response
    {
        return $this->render('consulta/show.html.twig', [
            'consultum' => $consultum,
            'paciente' => $consultum->getPaciente(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consulta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConsultaPendingType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/edit.html.twig', [
            'consultum' => $consultum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_consulta_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConsultaCancelType::class, $consultum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consultum->setEstadoConsulta(ConsultaEstados::CANCELED);
            $entityManager->persist($consultum);
            $entityManager->flush();

            $this->addFlash('success', 'Consulta Cancelada.');
            return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consulta/cancel.html.twig', [
            'entity' => $consultum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consulta_delete', methods: ['POST'])]
    public function delete(Request $request, Consulta $consultum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$consultum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($consultum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consulta_index', [], Response::HTTP_SEE_OTHER);
    }
}
