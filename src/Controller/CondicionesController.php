<?php

namespace App\Controller;

use App\Entity\Condiciones;
use App\Entity\Discapacidades;
use App\Entity\StatusRecord;
use App\Form\CondicionesType;
use App\Repository\CondicionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/condiciones')]
final class CondicionesController extends AbstractController
{
    #[Route(name: 'app_condiciones_index', methods: ['GET'])]
    public function index(CondicionesRepository $condicionesRepository): Response
    {
        return $this->render('condiciones/index.html.twig', [
            'entities' => $condicionesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_condiciones_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $condicione = new Condiciones();
        $form = $this->createForm(CondicionesType::class, $condicione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($condicione);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_condiciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condiciones/new.html.twig', [
            'entity' => $condicione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condiciones_show', methods: ['GET'])]
    public function show(Condiciones $condicione, EntityManagerInterface $entityManager): Response
    {
        $condicion = $entityManager->getRepository(Condiciones::class)->findOneBy([
            'id' => $condicione->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$condicion) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_condiciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condiciones/show.html.twig', [
            'entity' => $condicione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_condiciones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Condiciones $condicione, EntityManagerInterface $entityManager): Response
    {
        $condicion = $entityManager->getRepository(Condiciones::class)->findOneBy([
            'id' => $condicione->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$condicion) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_condiciones_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(CondicionesType::class, $condicione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado');
            return $this->redirectToRoute('app_condiciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('condiciones/edit.html.twig', [
            'entity' => $condicione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_condiciones_delete', methods: ['POST'])]
    public function delete(Request $request, Condiciones $condicion, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $condicion->getId(), $submittedToken)) {
            $condicion->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($condicion);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
