<?php

namespace App\Controller;

use App\Entity\Discapacidades;
use App\Entity\StatusRecord;
use App\Form\DiscapacidadesType;
use App\Repository\DiscapacidadesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/discapacidades')]
final class DiscapacidadesController extends AbstractController
{
    #[Route(name: 'app_discapacidades_index', methods: ['GET'])]
    public function index(DiscapacidadesRepository $discapacidadesRepository): Response
    {
        return $this->render('discapacidades/index.html.twig', [
            'entities' => $discapacidadesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_discapacidades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $discapacidade = new Discapacidades();
        $form = $this->createForm(DiscapacidadesType::class, $discapacidade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($discapacidade);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discapacidades/new.html.twig', [
            'entity' => $discapacidade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_discapacidades_show', methods: ['GET'])]
    public function show(Discapacidades $discapacidades, EntityManagerInterface $entityManager): Response
    {
        $discapacidad = $entityManager->getRepository(Discapacidades::class)->findOneBy([
            'id' => $discapacidades->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$discapacidad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discapacidades/show.html.twig', [
            'entity' => $discapacidad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_discapacidades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Discapacidades $discapacidades, EntityManagerInterface $entityManager): Response
    {
        $discapacidad = $entityManager->getRepository(Discapacidades::class)->findOneBy([
            'id' => $discapacidades->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$discapacidad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(DiscapacidadesType::class, $discapacidad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado');
            return $this->redirectToRoute('app_discapacidades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discapacidades/edit.html.twig', [
            'entity' => $discapacidad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_discapacidades_delete', methods: ['POST'])]
    public function delete(Request $request, Discapacidades $discapacidad, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $discapacidad->getId(), $submittedToken)) {
            $discapacidad->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($discapacidad);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
