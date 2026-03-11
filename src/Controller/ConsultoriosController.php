<?php

namespace App\Controller;

use App\Entity\Consultorios;
use App\Entity\Especialidades;
use App\Entity\StatusRecord;
use App\Form\ConsultoriosType;
use App\Repository\ConsultoriosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consultorios')]
final class ConsultoriosController extends AbstractController
{
    #[Route(name: 'app_consultorios_index', methods: ['GET'])]
    public function index(ConsultoriosRepository $consultoriosRepository): Response
    {
        return $this->render('consultorios/index.html.twig', [
            'entities' => $consultoriosRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_consultorios_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultorio = new Consultorios();
        $form = $this->createForm(ConsultoriosType::class, $consultorio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consultorio);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_consultorios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultorios/new.html.twig', [
            'consultorio' => $consultorio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultorios_show', methods: ['GET'])]
    public function show(Consultorios $consultorios, EntityManagerInterface $entityManager): Response
    {
        $consultorio = $entityManager->getRepository(Consultorios::class)->findOneBy([
            'id' => $consultorios->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$consultorio) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_consultorios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultorios/show.html.twig', [
            'entity' => $consultorio,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consultorios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consultorios $consultorios, EntityManagerInterface $entityManager): Response
    {
        $consultorio = $entityManager->getRepository(Consultorios::class)->findOneBy([
            'id' => $consultorios->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$consultorio) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_consultorios_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ConsultoriosType::class, $consultorio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado');
            return $this->redirectToRoute('app_consultorios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultorios/edit.html.twig', [
            'consultorio' => $consultorio,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultorios_delete', methods: ['POST'])]
    public function delete(Request $request, Consultorios $consultorio, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $consultorio->getId(), $submittedToken)) {
            $consultorio->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($consultorio);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
