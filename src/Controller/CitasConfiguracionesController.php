<?php

namespace App\Controller;

use App\Entity\CitasConfiguraciones;
use App\Entity\StatusRecord;
use App\Form\CitasConfiguracionesType;
use App\Repository\CitasConfiguracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TurnoDoctorRepository;

#[Route('/citas/configuraciones')]
final class CitasConfiguracionesController extends AbstractController
{
    #[Route('/api/validate-capacity', name: 'app_citas_configuraciones_validate_capacity', methods: ['POST'])]
    public function validateCapacity(Request $request, TurnoDoctorRepository $turnoRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $especialidadId = $data['especialidad'] ?? null;
        $duracionCita = $data['duracionCita'] ?? null;
        $tiempoReceso = $data['tiempoReceso'] ?? 0;

        if (!$especialidadId || !$duracionCita) {
            return new JsonResponse(['valid' => false, 'message' => 'Faltan datos de especialidad o duración.']);
        }

        $duracionCita = (int)$duracionCita;
        $tiempoReceso = (int)$tiempoReceso;
        $totalSlotTime = $duracionCita + $tiempoReceso;

        if ($totalSlotTime <= 0) {
            return new JsonResponse(['valid' => false, 'message' => 'El tiempo total por cita debe ser mayor a 0.']);
        }

        // Obtener status activo
        $statusActivo = $em->getRepository(StatusRecord::class)->getActive();

        // Obtener todos los turnos activos de la especialidad
        $turnos = $turnoRepo->findBy([
            'especialidad' => $especialidadId,
            'isActive' => true,
            'status' => $statusActivo
        ]);

        if (empty($turnos)) {
            return new JsonResponse([
                'valid' => true, 
                'maxCapacity' => 0, 
                'message' => 'No hay médicos activos configurados para esta especialidad.'
            ]);
        }

        // Agrupar turnos por día de la semana para encontrar el día con mayor capacidad
        $capacitiesByDay = array_fill(1, 7, 0);

        foreach ($turnos as $turno) {
            $currentTime = \DateTime::createFromFormat('H:i:s', $turno->getStartTime()->format('H:i:s'));
            $endTime = \DateTime::createFromFormat('H:i:s', $turno->getEndTime()->format('H:i:s'));
            
            $slotsCount = 0;
            while ($currentTime < $endTime) {
                $slotEnd = (clone $currentTime)->modify("+$duracionCita minutes");
                if ($slotEnd > $endTime) break;
                
                $slotsCount++;
                $currentTime->modify("+$totalSlotTime minutes");
            }

            $capacitiesByDay[$turno->getDayOfWeek()] += $slotsCount;
        }

        $maxCapacity = max($capacitiesByDay);

        return new JsonResponse([
            'valid' => true,
            'maxCapacity' => $maxCapacity
        ]);
    }
    #[Route(name: 'app_citas_configuraciones_index', methods: ['GET'])]
    public function index(CitasConfiguracionesRepository $citasConfiguracionesRepository, EntityManagerInterface $em): Response
    {
        $statusRemove = $em->getRepository(StatusRecord::class)->getRemove();
        $qb = $citasConfiguracionesRepository->createQueryBuilder('c');
        
        $entities = $qb->where('c.status != :rem OR c.status IS NULL')
           ->setParameter('rem', $statusRemove)
           ->getQuery()
           ->getResult();

        return $this->render('citas_configuraciones/index.html.twig', [
            'entities' => $entities,
        ]);
    }

    #[Route('/new', name: 'app_citas_configuraciones_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $citasConfiguracione = new CitasConfiguraciones();
        $form = $this->createForm(CitasConfiguracionesType::class, $citasConfiguracione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $citasConfiguracione->setStatus($entityManager->getRepository(StatusRecord::class)->getActive());
            // Por defecto, isActive es false.
            $entityManager->persist($citasConfiguracione);
            $entityManager->flush();

            $this->addFlash('success', 'Configuración Creada. Por defecto está inactiva.');
            return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_configuraciones/new.html.twig', [
            'entity' => $citasConfiguracione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle-active', name: 'app_citas_configuraciones_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, CitasConfiguraciones $citasConfiguracione, EntityManagerInterface $entityManager, \Symfony\Component\Validator\Validator\ValidatorInterface $validator): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$citasConfiguracione->getId(), $request->getPayload()->getString('_token'))) {
            if ($citasConfiguracione->isActive()) {
                $citasConfiguracione->setIsActive(false);
                $entityManager->flush();
                $this->addFlash('success', 'Configuración desactivada exitosamente.');
            } else {
                $citasConfiguracione->setIsActive(true);
                $errors = $validator->validate($citasConfiguracione);
                
                if (count($errors) > 0) {
                    $citasConfiguracione->setIsActive(false);
                    $this->addFlash('danger', 'Error: ' . $errors[0]->getMessage());
                } else {
                    $entityManager->flush();
                    $this->addFlash('success', 'Configuración activada exitosamente.');
                }
            }
        } else {
            $this->addFlash('danger', 'Token CSRF inválido.');
        }

        return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_citas_configuraciones_show', methods: ['GET'])]
    public function show(CitasConfiguraciones $citasConfiguracione): Response
    {
        return $this->render('citas_configuraciones/show.html.twig', [
            'citas_configuracione' => $citasConfiguracione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_citas_configuraciones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CitasConfiguraciones $citasConfiguracione, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CitasConfiguracionesType::class, $citasConfiguracione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Configuracion Establecida.');
            return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_configuraciones/edit.html.twig', [
            'entity' => $citasConfiguracione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_citas_configuraciones_delete', methods: ['POST'])]
    public function delete(Request $request, CitasConfiguraciones $citasConfiguracione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$citasConfiguracione->getId(), $request->getPayload()->getString('_token'))) {
            $citasConfiguracione->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $citasConfiguracione->setIsActive(false);
            $entityManager->persist($citasConfiguracione);
            $entityManager->flush();
            $this->addFlash('success', 'Configuracion enviada a la papelera.');
        }

        return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
    }
}
