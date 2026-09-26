<?php

namespace App\Controller;

use App\Entity\TurnoDoctor;
use App\Form\TurnoDoctorType;
use App\Repository\TurnoDoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                $turnoDoctor->setIsActive(true);
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
            if ($request->headers->get('X-Requested-With') !== 'XMLHttpRequest') {
                $entityManager->flush();

                $this->addFlash('success', 'Turno modificado.');
                return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('turno_doctor/edit.html.twig', [
            'turno_doctor' => $turnoDoctor,
            'form' => $form,
        ]);
    }

    #[Route('/turnos/json', name: 'app_turno_doctor_json', methods: ['GET'])]
    public function getTurnosJson(TurnoDoctorRepository $repo): JsonResponse
    {
        $turnos = $repo->findBy(['isActive' => true]);
        $events = [];

        foreach ($turnos as $turno) {

            // Generate a consistent hex color from the specialty name
            $hash = md5($turno->getEspecialidad()->getNombre());
            $color = '#' . substr($hash, 0, 6);

            $events[] = [
                'title' => $turno->getDoctor()->getNombreCompleto() . ' (' . $turno->getEspecialidad()->getNombre() . ')',
                // FullCalendar maps Sunday=0, Monday=1. If your DB uses ISO (Monday=1, Sunday=7), map Sunday to 0.
                'daysOfWeek' => [$turno->getDayOfWeek() === 7 ? 0 : $turno->getDayOfWeek()],
                'startTime' => $turno->getStartTime()->format('H:i'),
                'endTime' => $turno->getEndTime()->format('H:i'),
                'url' => $this->generateUrl('app_turno_doctor_edit', ['id' => $turno->getId()]),
                'color' => $color,
            ];
        }

        return new JsonResponse($events);
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
