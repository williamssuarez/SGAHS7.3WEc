<?php

namespace App\Controller;

use App\Entity\Alergenos;
use App\Entity\StatusRecord;
use App\Entity\Inmunizaciones;
use App\Form\InmunizacionesType;
use App\Repository\InmunizacionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inmunizaciones')]
final class InmunizacionesController extends AbstractController
{
    #[Route(name: 'app_inmunizaciones_index', methods: ['GET'])]
    public function index(InmunizacionesRepository $inmunizacionesRepository): Response
    {
        return $this->render('inmunizaciones/index.html.twig', [
            'entities' => $inmunizacionesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_inmunizaciones_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inmunizacion = new Inmunizaciones();
        $form = $this->createForm(InmunizacionesType::class, $inmunizacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inmunizacion);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_inmunizaciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inmunizaciones/new.html.twig', [
            'entity' => $inmunizacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inmunizaciones_show', methods: ['GET'])]
    public function show(Inmunizaciones $inmunizaciones, EntityManagerInterface $entityManager): Response
    {
        $inmunizacion = $entityManager->getRepository(Inmunizaciones::class)->findOneBy([
            'id' => $inmunizaciones->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$inmunizacion) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_inmunizaciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inmunizaciones/show.html.twig', [
            'entity' => $inmunizacion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_inmunizaciones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inmunizaciones $inmunizaciones, EntityManagerInterface $entityManager): Response
    {
        $inmunizacion = $entityManager->getRepository(Inmunizaciones::class)->findOneBy([
            'id' => $inmunizaciones->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$inmunizacion) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_inmunizaciones_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(InmunizacionesType::class, $inmunizacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_inmunizaciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('inmunizaciones/edit.html.twig', [
            'entity' => $inmunizacion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_inmunizaciones_delete', methods: ['POST'])]
    public function delete(Request $request, Inmunizaciones $inmunizaciones, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $inmunizaciones->getId(), $submittedToken)) {
            $inmunizaciones->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($inmunizaciones);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
