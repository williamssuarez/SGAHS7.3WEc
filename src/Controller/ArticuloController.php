<?php

namespace App\Controller;

use App\Entity\Alergenos;
use App\Entity\Articulo;
use App\Entity\StatusRecord;
use App\Form\ArticuloType;
use App\Repository\ArticuloRepository;
use App\Repository\QuirofanoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/articulo')]
final class ArticuloController extends AbstractController
{
    #[Route(name: 'app_articulo_index', methods: ['GET'])]
    public function index(ArticuloRepository $articuloRepository): Response
    {
        return $this->render('articulo/index.html.twig', [
            'entities' => $articuloRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_articulo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $articulo = new Articulo();
        $form = $this->createForm(ArticuloType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($articulo);
            $entityManager->flush();

            $this->addFlash('success', 'Articulo creado correctamente');
            return $this->redirectToRoute('app_articulo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articulo/new.html.twig', [
            'articulo' => $articulo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articulo_show', methods: ['GET'])]
    public function show(Articulo $articulo): Response
    {
        return $this->render('articulo/show.html.twig', [
            'articulo' => $articulo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_articulo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articulo $articulo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticuloType::class, $articulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Articulo editado correctamente');
            return $this->redirectToRoute('app_articulo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articulo/edit.html.twig', [
            'articulo' => $articulo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articulo_delete', methods: ['POST'])]
    public function delete(Request $request, Articulo $articulo, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $articulo->getId(), $submittedToken)) {
            $articulo->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($articulo);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
