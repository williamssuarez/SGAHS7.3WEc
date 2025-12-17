<?php

namespace App\Controller;

use App\Entity\Alergenos;
use App\Entity\Reacciones;
use App\Entity\StatusRecord;
use App\Form\ReaccionesType;
use App\Repository\ReaccionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reacciones')]
final class ReaccionesController extends AbstractController
{
    #[Route(name: 'app_reacciones_index', methods: ['GET'])]
    public function index(ReaccionesRepository $reaccionesRepository): Response
    {
        return $this->render('reacciones/index.html.twig', [
            'entities' => $reaccionesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_reacciones_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reaccione = new Reacciones();
        $form = $this->createForm(ReaccionesType::class, $reaccione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reaccione);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_reacciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reacciones/new.html.twig', [
            'entity' => $reaccione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reacciones_show', methods: ['GET'])]
    public function show(Reacciones $reaccione, EntityManagerInterface $entityManager): Response
    {
        if ($reaccione->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_reacciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reacciones/show.html.twig', [
            'entity' => $reaccione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reacciones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reacciones $reaccione, EntityManagerInterface $entityManager): Response
    {
        if ($reaccione->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_reacciones_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(ReaccionesType::class, $reaccione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_reacciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reacciones/edit.html.twig', [
            'entity' => $reaccione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reacciones_delete', methods: ['POST'])]
    public function delete(Request $request, Reacciones $reacciones, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $reacciones->getId(), $submittedToken)) {
            $reacciones->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($reacciones);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
