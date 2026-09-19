<?php

namespace App\Controller;

use App\Entity\TurnoDoctor;
use App\Form\TurnoDoctorType;
use App\Repository\TurnoDoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/turno/doctor')]
final class TurnoDoctorController extends AbstractController
{
    #[Route(name: 'app_turno_doctor_index', methods: ['GET'])]
    public function index(TurnoDoctorRepository $turnoDoctorRepository): Response
    {
        return $this->render('turno_doctor/index.html.twig', [
            'entities' => $turnoDoctorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_turno_doctor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $turnoDoctor = new TurnoDoctor();
        $form = $this->createForm(TurnoDoctorType::class, $turnoDoctor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                $entityManager->persist($turnoDoctor);
                $entityManager->flush();

                $this->addFlash('success', 'Turno guardado.');
                return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('turno_doctor/new.html.twig', [
            'turno_doctor' => $turnoDoctor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_turno_doctor_show', methods: ['GET'])]
    public function show(TurnoDoctor $turnoDoctor): Response
    {
        return $this->render('turno_doctor/show.html.twig', [
            'entities' => $turnoDoctor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_turno_doctor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TurnoDoctor $turnoDoctor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TurnoDoctorType::class, $turnoDoctor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('turno_doctor/edit.html.twig', [
            'turno_doctor' => $turnoDoctor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_turno_doctor_delete', methods: ['POST'])]
    public function delete(Request $request, TurnoDoctor $turnoDoctor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$turnoDoctor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($turnoDoctor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
    }
}
