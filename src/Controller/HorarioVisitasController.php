<?php

namespace App\Controller;

use App\Entity\HorarioVisitas;
use App\Entity\StatusRecord;
use App\Form\HorarioVisitasType;
use App\Repository\HorarioVisitasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/horario/visitas')]
final class HorarioVisitasController extends AbstractController
{
    #[Route(name: 'app_horario_visitas_index', methods: ['GET'])]
    public function index(HorarioVisitasRepository $horarioVisitasRepository, EntityManagerInterface $entityManager): Response
    {
        return $this->render('horario_visitas/index.html.twig', [
            'entities' => $horarioVisitasRepository->findBy(['status' => $entityManager->getRepository(StatusRecord::class)->getActive()],['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_horario_visitas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $horarioVisita = new HorarioVisitas();
        $form = $this->createForm(HorarioVisitasType::class, $horarioVisita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $config = $entityManager->getRepository(HorarioVisitas::class)->findOneBy([
                'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
                'area' => $horarioVisita->getArea()->getId(),
                'diaSemana' => $horarioVisita->getDiaSemana(),
            ]);

            if ($config) {
                $this->addFlash('danger', 'Este horario ya existe.');
                return $this->redirectToRoute('app_horario_visitas_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($horarioVisita);
            $entityManager->flush();

            $this->addFlash('success', 'Registro creado satisfactoriamente.');
            return $this->redirectToRoute('app_horario_visitas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('horario_visitas/new.html.twig', [
            'entity' => $horarioVisita,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_horario_visitas_show', methods: ['GET'])]
    public function show(HorarioVisitas $horarioVisita): Response
    {
        return $this->render('horario_visitas/show.html.twig', [
            'horario_visita' => $horarioVisita,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_horario_visitas_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HorarioVisitas $horarioVisita, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HorarioVisitasType::class, $horarioVisita);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_horario_visitas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('horario_visitas/edit.html.twig', [
            'entity' => $horarioVisita,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_horario_visitas_delete', methods: ['POST'])]
    public function delete(Request $request, HorarioVisitas $horarioVisitas, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $horarioVisitas->getId(), $submittedToken)) {
            $horarioVisitas->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($horarioVisitas);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
