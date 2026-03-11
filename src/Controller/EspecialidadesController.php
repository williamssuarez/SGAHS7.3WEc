<?php

namespace App\Controller;

use App\Entity\Condiciones;
use App\Entity\Especialidades;
use App\Entity\StatusRecord;
use App\Form\EspecialidadesType;
use App\Repository\EspecialidadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/especialidades')]
final class EspecialidadesController extends AbstractController
{
    #[Route(name: 'app_especialidades_index', methods: ['GET'])]
    public function index(EspecialidadesRepository $especialidadesRepository): Response
    {
        return $this->render('especialidades/index.html.twig', [
            'entities' => $especialidadesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_especialidades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $especialidade = new Especialidades();
        $form = $this->createForm(EspecialidadesType::class, $especialidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($especialidade);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_especialidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('especialidades/new.html.twig', [
            'entity' => $especialidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_especialidades_show', methods: ['GET'])]
    public function show(Especialidades $especialidade, EntityManagerInterface $entityManager): Response
    {
        $especialidad = $entityManager->getRepository(Especialidades::class)->findOneBy([
            'id' => $especialidade->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$especialidad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_especialidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('especialidades/show.html.twig', [
            'entity' => $especialidad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_especialidades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Especialidades $especialidade, EntityManagerInterface $entityManager): Response
    {
        $especialidad = $entityManager->getRepository(Especialidades::class)->findOneBy([
            'id' => $especialidade->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$especialidad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_especialidades_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(EspecialidadesType::class, $especialidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado');
            return $this->redirectToRoute('app_especialidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('especialidades/edit.html.twig', [
            'especialidade' => $especialidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_especialidades_delete', methods: ['POST'])]
    public function delete(Request $request, Especialidades $especialidad, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $especialidad->getId(), $submittedToken)) {
            $especialidad->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($especialidad);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
