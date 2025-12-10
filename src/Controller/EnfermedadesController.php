<?php

namespace App\Controller;

use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use App\Form\EnfermedadesType;
use App\Repository\EnfermedadesRepository;
use App\Repository\StatusRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/enfermedades')]
final class EnfermedadesController extends AbstractController
{
    #[Route(name: 'app_enfermedades_index', methods: ['GET'])]
    public function index(EnfermedadesRepository $enfermedadesRepository): Response
    {
        return $this->render('enfermedades/index.html.twig', [
            'entities' => $enfermedadesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_enfermedades_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $enfermedade = new Enfermedades();
        $form = $this->createForm(EnfermedadesType::class, $enfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($enfermedade);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enfermedades/new.html.twig', [
            'entity' => $enfermedade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enfermedades_show', methods: ['GET'])]
    public function show(Enfermedades $enfermedade, EntityManagerInterface $entityManager): Response
    {
        $enfermedad = $entityManager->getRepository(Enfermedades::class)->findOneBy([
            'id' => $enfermedade->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$enfermedad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enfermedades/show.html.twig', [
            'entity' => $enfermedad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_enfermedades_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Enfermedades $enfermedade, EntityManagerInterface $entityManager): Response
    {
        $enfermedad = $entityManager->getRepository(Enfermedades::class)->findOneBy([
            'id' => $enfermedade->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$enfermedad) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(EnfermedadesType::class, $enfermedade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('enfermedades/edit.html.twig', [
            'entity' => $enfermedade,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_enfermedades_delete', methods: ['POST'])]
    public function delete(Request $request, Enfermedades $enfermedade, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $enfermedade->getId(), $submittedToken)) {
            $enfermedade->setStatus($recordRepository->getRemove());
            $entityManager->persist($enfermedade);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
        //return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
    }
}
