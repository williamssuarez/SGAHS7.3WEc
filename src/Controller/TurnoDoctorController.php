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
    public function index(TurnoDoctorRepository $turnoDoctorRepository, EntityManagerInterface $entityManager): Response
    {
        return $this->render('turno_doctor/index.html.twig', [
            'entities' => $turnoDoctorRepository->findBy([
                'status' => $entityManager->getRepository(\App\Entity\StatusRecord::class)->getActive()
            ]),
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

    #[Route('/api/validate-conflict', name: 'app_turno_doctor_validate_conflict', methods: ['POST'])]
    public function validateConflict(Request $request, TurnoDoctorRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $doctorId = $data['doctor'] ?? null;
        $consultorioId = $data['consultorio'] ?? null;
        $dayOfWeek = $data['dayOfWeek'] ?? null;
        $startTimeStr = $data['startTime'] ?? null;
        $endTimeStr = $data['endTime'] ?? null;
        $excludeId = $data['excludeId'] ?? null;

        if (!$doctorId || !$consultorioId || !$dayOfWeek || !$startTimeStr || !$endTimeStr) {
            return new JsonResponse(['valid' => false, 'message' => 'Faltan datos requeridos.']);
        }

        $startTime = \DateTime::createFromFormat('H:i', $startTimeStr);
        $endTime = \DateTime::createFromFormat('H:i', $endTimeStr);

        if (!$startTime || !$endTime) {
            return new JsonResponse(['valid' => false, 'message' => 'Formato de hora inválido.']);
        }

        if ($startTime >= $endTime) {
            return new JsonResponse(['valid' => false, 'message' => 'La hora de inicio debe ser anterior a la hora de fin.']);
        }

        // Buscar posibles conflictos en la BD
        $qb = $repo->createQueryBuilder('t')
            ->where('t.dayOfWeek = :day')
            ->andWhere('t.isActive = :active')
            ->setParameter('day', $dayOfWeek)
            ->setParameter('active', true);

        if ($excludeId) {
            $qb->andWhere('t.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $turnos = $qb->getQuery()->getResult();

        foreach ($turnos as $turno) {
            // Comprobar solapamiento de tiempo
            // Un solapamiento ocurre si (A.start < B.end) Y (A.end > B.start)
            $s1 = $startTime->format('H:i:s');
            $e1 = $endTime->format('H:i:s');
            $s2 = $turno->getStartTime()->format('H:i:s');
            $e2 = $turno->getEndTime()->format('H:i:s');

            if ($s1 < $e2 && $e1 > $s2) {
                if ($turno->getDoctor()->getId() == $doctorId) {
                    return new JsonResponse(['valid' => false, 'message' => 'El doctor ya tiene un turno asignado que se solapa en este horario.']);
                }
                if ($turno->getConsultorio()->getId() == $consultorioId) {
                    return new JsonResponse(['valid' => false, 'message' => 'El consultorio ya está ocupado por otro turno en este horario.']);
                }
            }
        }

        return new JsonResponse(['valid' => true, 'message' => 'Turno válido y sin conflictos.']);
    }

    #[Route('/turnos/json', name: 'app_turno_doctor_json', methods: ['GET'])]
    public function getTurnosJson(TurnoDoctorRepository $repo, EntityManagerInterface $entityManager): JsonResponse
    {
        $turnos = $repo->findBy([
            'isActive' => true,
            'status' => $entityManager->getRepository(\App\Entity\StatusRecord::class)->getActive()
        ]);
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

    #[Route('/{id}/toggle-active', name: 'app_turno_doctor_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, TurnoDoctor $turnoDoctor, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$turnoDoctor->getId(), $request->getPayload()->getString('_token'))) {
            if ($turnoDoctor->isActive()) {
                // Desactivar siempre está permitido
                $turnoDoctor->setIsActive(false);
                $entityManager->flush();
                $this->addFlash('success', 'Turno desactivado exitosamente.');
            } else {
                // Para activar, primero comprobamos que no haya solapamientos
                $turnoDoctor->setIsActive(true);
                
                // Usar el Validator (que disparará el NoShiftOverlapValidator)
                $errors = $validator->validate($turnoDoctor);
                
                if (count($errors) > 0) {
                    // Hay un conflicto, revertimos el cambio y avisamos
                    $turnoDoctor->setIsActive(false);
                    $this->addFlash('danger', 'Error al activar el turno: ' . $errors[0]->getMessage());
                } else {
                    // No hay conflicto, se guarda la activación
                    $entityManager->flush();
                    $this->addFlash('success', 'Turno activado exitosamente.');
                }
            }
        } else {
            $this->addFlash('danger', 'Token CSRF inválido.');
        }

        return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_turno_doctor_delete', methods: ['POST'])]
    public function delete(Request $request, TurnoDoctor $turnoDoctor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$turnoDoctor->getId(), $request->getPayload()->getString('_token'))) {
            $turnoDoctor->setStatus($entityManager->getRepository(\App\Entity\StatusRecord::class)->getRemove());
            $entityManager->persist($turnoDoctor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_turno_doctor_index', [], Response::HTTP_SEE_OTHER);
    }
}
