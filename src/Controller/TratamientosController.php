<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\StatusRecord;
use App\Entity\Tratamientos;
use App\Form\TratamientosType;
use App\Repository\TratamientosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tratamientos')]
final class TratamientosController extends AbstractController
{
    #[Route(name: 'app_tratamientos_index', methods: ['GET'])]
    public function index(TratamientosRepository $tratamientosRepository): Response
    {
        return $this->render('tratamientos/index.html.twig', [
            'entities' => $tratamientosRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_tratamientos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tratamiento = new Tratamientos();
        $form = $this->createForm(TratamientosType::class, $tratamiento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tratamiento);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tratamientos/new.html.twig', [
            'entity' => $tratamiento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tratamientos_show', methods: ['GET'])]
    public function show(Tratamientos $tratamientos, EntityManagerInterface $entityManager): Response
    {
        $tratamiento = $entityManager->getRepository(Tratamientos::class)->findOneBy([
            'id' => $tratamientos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$tratamiento) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tratamientos/show.html.twig', [
            'entity' => $tratamiento,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tratamientos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tratamientos $tratamientos, EntityManagerInterface $entityManager): Response
    {
        $tratamiento = $entityManager->getRepository(Tratamientos::class)->findOneBy([
            'id' => $tratamientos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$tratamiento) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(TratamientosType::class, $tratamiento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_tratamientos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tratamientos/edit.html.twig', [
            'entity' => $tratamiento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tratamientos_delete', methods: ['POST'])]
    public function delete(Request $request, Tratamientos $tratamiento, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $tratamiento->getId(), $submittedToken)) {
            $tratamiento->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($tratamiento);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
