<?php

namespace App\Controller;

use App\Entity\TurnoDoctor;
use App\Form\TurnoDoctorType;
use App\Repository\StatusRecordRepository;
use App\Repository\TurnoDoctorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/turno/doctor')]
final class TurnoDoctorController extends AbstractController
{
    private const DAY_NAMES = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    /**
     * Paleta Apple HIG curada para especialidades en FullCalendar y etiquetas de tabla.
     */
    private const APPLE_PALETTE = [
        ['bg' => '#0071e3', 'softBg' => 'rgba(0, 113, 227, 0.13)', 'text' => '#005bb5', 'darkText' => '#64b5ff'],
        ['bg' => '#28a745', 'softBg' => 'rgba(52, 199, 89, 0.14)', 'text' => '#1e7e34', 'darkText' => '#5ee37d'],
        ['bg' => '#5856d6', 'softBg' => 'rgba(88, 86, 214, 0.13)', 'text' => '#4240b8', 'darkText' => '#9f9eff'],
        ['bg' => '#0a84ff', 'softBg' => 'rgba(10, 132, 255, 0.13)', 'text' => '#0062cc', 'darkText' => '#5ac8fa'],
        ['bg' => '#af52de', 'softBg' => 'rgba(175, 82, 222, 0.13)', 'text' => '#8932b8', 'darkText' => '#da8fff'],
        ['bg' => '#ff9500', 'softBg' => 'rgba(255, 149, 0, 0.14)', 'text' => '#a85500', 'darkText' => '#ffb340'],
        ['bg' => '#ff2d55', 'softBg' => 'rgba(255, 45, 85, 0.13)', 'text' => '#c2183a', 'darkText' => '#ff6482'],
        ['bg' => '#00a6b4', 'softBg' => 'rgba(0, 166, 180, 0.14)', 'text' => '#00717c', 'darkText' => '#5eead4'],
    ];

    #[Route(name: 'app_turno_doctor_index', methods: ['GET'])]
    public function index(TurnoDoctorRepository $turnoDoctorRepository, StatusRecordRepository $statusRepo): Response
    {
        $entities = $turnoDoctorRepository->findBy([
            'status' => $statusRepo->getActive(),
        ], [
            'dayOfWeek' => 'ASC',
            'startTime' => 'ASC',
        ]);

        $activeCount = 0;
        $doctorIds = [];
        $consultorioIds = [];
        $specialtyColors = [];

        foreach ($entities as $entity) {
            if ($entity->isActive()) {
                $activeCount++;
                if ($entity->getDoctor()?->getId()) {
                    $doctorIds[$entity->getDoctor()->getId()] = true;
                }
                if ($entity->getConsultorio()?->getId()) {
                    $consultorioIds[$entity->getConsultorio()->getId()] = true;
                }
            }

            $esp = $entity->getEspecialidad();
            if ($esp && $esp->getId() && !isset($specialtyColors[$esp->getId()])) {
                $specialtyColors[$esp->getId()] = $this->resolveSpecialtyColor((string) $esp->getNombre());
            }
        }

        return $this->render('turno_doctor/index.html.twig', [
            'entities' => $entities,
            'days' => self::DAY_NAMES,
            'specialty_colors' => $specialtyColors,
            'kpis' => [
                'activeCount' => $activeCount,
                'totalCount' => count($entities),
                'doctorCount' => count($doctorIds),
                'consultorioCount' => count($consultorioIds),
            ],
        ]);
    }

    #[Route('/new', name: 'app_turno_doctor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $turnoDoctor = new TurnoDoctor();
        $turnoDoctor->setIsActive(true);

        if ($request->isMethod('GET')) {
            $day = $request->query->getInt('day', 0);
            if ($day >= 1 && $day <= 7) {
                $turnoDoctor->setDayOfWeek($day);
            }

            $parsedStart = $this->parseTimeParam($request->query->getString('start', ''));
            $parsedEnd = $this->parseTimeParam($request->query->getString('end', ''));

            if ($parsedStart !== null) {
                $turnoDoctor->setStartTime($parsedStart);
            }
            if ($parsedEnd !== null && ($parsedStart === null || $parsedEnd > $parsedStart)) {
                $turnoDoctor->setEndTime($parsedEnd);
            }
        }

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
        $espName = $turnoDoctor->getEspecialidad()?->getNombre() ?? 'Sin especialidad';

        return $this->render('turno_doctor/show.html.twig', [
            'turno_doctor' => $turnoDoctor,
            'day_label' => self::DAY_NAMES[$turnoDoctor->getDayOfWeek()] ?? 'No definido',
            'duration_label' => $this->formatDuration($turnoDoctor->getStartTime(), $turnoDoctor->getEndTime()),
            'specialty_color' => $this->resolveSpecialtyColor($espName),
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
    public function validateConflict(
        Request $request,
        TurnoDoctorRepository $repo,
        StatusRecordRepository $statusRepo
    ): JsonResponse {
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

        $startTime = $this->parseTimeParam((string) $startTimeStr);
        $endTime = $this->parseTimeParam((string) $endTimeStr);

        if (!$startTime || !$endTime) {
            return new JsonResponse(['valid' => false, 'message' => 'Formato de hora inválido.']);
        }

        if ($startTime >= $endTime) {
            return new JsonResponse(['valid' => false, 'message' => 'La hora de inicio debe ser anterior a la hora de fin.']);
        }

        $qb = $repo->createQueryBuilder('t')
            ->where('t.dayOfWeek = :day')
            ->andWhere('t.isActive = :active')
            ->andWhere('t.status = :status')
            ->setParameter('day', $dayOfWeek)
            ->setParameter('active', true)
            ->setParameter('status', $statusRepo->getActive());

        if ($excludeId) {
            $qb->andWhere('t.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        $turnos = $qb->getQuery()->getResult();

        foreach ($turnos as $turno) {
            $s1 = $startTime->format('H:i:s');
            $e1 = $endTime->format('H:i:s');
            $s2 = $turno->getStartTime()?->format('H:i:s');
            $e2 = $turno->getEndTime()?->format('H:i:s');

            if ($s2 && $e2 && $s1 < $e2 && $e1 > $s2) {
                if ($turno->getDoctor()?->getId() == $doctorId) {
                    return new JsonResponse(['valid' => false, 'message' => 'El doctor ya tiene un turno asignado que se solapa en este horario.']);
                }
                if ($turno->getConsultorio()?->getId() == $consultorioId) {
                    return new JsonResponse(['valid' => false, 'message' => 'El consultorio ya está ocupado por otro turno en este horario.']);
                }
            }
        }

        return new JsonResponse(['valid' => true, 'message' => 'Turno válido y sin conflictos.']);
    }

    #[Route('/turnos/json', name: 'app_turno_doctor_json', methods: ['GET'])]
    public function getTurnosJson(
        TurnoDoctorRepository $repo,
        StatusRecordRepository $statusRepo,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $criteria = ['status' => $statusRepo->getActive()];
        if (!$request->query->getBoolean('include_inactive', false)) {
            $criteria['isActive'] = true;
        }

        $turnos = $repo->findBy($criteria, [
            'dayOfWeek' => 'ASC',
            'startTime' => 'ASC',
        ]);

        $events = [];
        foreach ($turnos as $turno) {
            $doctor = $turno->getDoctor();
            $especialidad = $turno->getEspecialidad();
            $consultorio = $turno->getConsultorio();

            $espName = $especialidad?->getNombre() ?? 'Sin especialidad';
            $doctorName = $doctor?->getNombreCompleto()
                ?: trim(($doctor?->getNombre() ?? '') . ' ' . ($doctor?->getApellido() ?? ''))
                ?: 'Médico asignado';

            $initials = mb_strtoupper(
                mb_substr(trim((string) $doctor?->getNombre()), 0, 1)
                . mb_substr(trim((string) $doctor?->getApellido()), 0, 1)
            ) ?: 'DR';

            $palette = $this->resolveSpecialtyColor($espName);
            $isActive = (bool) $turno->isActive();
            $start = $turno->getStartTime();
            $end = $turno->getEndTime();
            $startFormatted = $start ? $start->format('H:i') : '00:00';
            $endFormatted = $end ? $end->format('H:i') : '00:00';

            $events[] = [
                'id' => (string) $turno->getId(),
                'title' => $doctorName . ' · ' . $espName,
                // FullCalendar mapea Domingo=0, Lunes=1..Sábado=6 desde ISO (1..7)
                'daysOfWeek' => [$turno->getDayOfWeek() === 7 ? 0 : (int) $turno->getDayOfWeek()],
                'startTime' => $startFormatted,
                'endTime' => $endFormatted,
                'backgroundColor' => $isActive ? $palette['bg'] : '#636366',
                'borderColor' => $isActive ? $palette['bg'] : '#8e8e93',
                'textColor' => '#ffffff',
                'classNames' => $isActive ? ['apple-fc-event'] : ['apple-fc-event', 'is-inactive'],
                'extendedProps' => [
                    'shiftId' => $turno->getId(),
                    'doctorId' => $doctor?->getId(),
                    'doctorName' => $doctorName,
                    'doctorInitials' => $initials,
                    'especialidadId' => $especialidad?->getId(),
                    'especialidadName' => $espName,
                    'consultorioName' => $consultorio?->getNombre() ?? 'Sin consultorio',
                    'consultorioUbicacion' => $consultorio?->getUbicacion(),
                    'dayLabel' => self::DAY_NAMES[$turno->getDayOfWeek()] ?? 'No definido',
                    'startFormatted' => $startFormatted,
                    'endFormatted' => $endFormatted,
                    'durationLabel' => $this->formatDuration($start, $end),
                    'isActive' => $isActive,
                    'colorHex' => $palette['bg'],
                    'showUrl' => $this->generateUrl('app_turno_doctor_show', ['id' => $turno->getId()]),
                    'editUrl' => $this->generateUrl('app_turno_doctor_edit', ['id' => $turno->getId()]),
                    'toggleUrl' => $this->generateUrl('app_turno_doctor_toggle_active', ['id' => $turno->getId()]),
                    'toggleToken' => $csrfTokenManager->getToken('toggle' . $turno->getId())->getValue(),
                    'deleteUrl' => $this->generateUrl('app_turno_doctor_delete', ['id' => $turno->getId()]),
                    'deleteToken' => $csrfTokenManager->getToken('delete' . $turno->getId())->getValue(),
                ],
            ];
        }

        return new JsonResponse($events);
    }

    private function resolveSpecialtyColor(string $specialtyName): array
    {
        $index = ((int) sprintf('%u', crc32(mb_strtolower(trim($specialtyName))))) % count(self::APPLE_PALETTE);
        return self::APPLE_PALETTE[$index];
    }

    private function parseTimeParam(string $value): ?\DateTime
    {
        $trimmed = trim($value);
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $trimmed)) {
            return null;
        }

        $dt = \DateTime::createFromFormat('!H:i', $trimmed);
        return $dt instanceof \DateTime ? $dt : null;
    }

    private function formatDuration(?\DateTimeInterface $start, ?\DateTimeInterface $end): string
    {
        if (!$start || !$end) {
            return '—';
        }

        $startMinutes = ((int) $start->format('H')) * 60 + (int) $start->format('i');
        $endMinutes = ((int) $end->format('H')) * 60 + (int) $end->format('i');
        $diff = $endMinutes - $startMinutes;
        if ($diff <= 0) {
            return '—';
        }

        $hours = intdiv($diff, 60);
        $mins = $diff % 60;
        if ($hours > 0 && $mins > 0) {
            return sprintf('%d h %02d min', $hours, $mins);
        }

        return $hours > 0 ? sprintf('%d h', $hours) : sprintf('%d min', $mins);
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
