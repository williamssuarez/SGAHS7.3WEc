<?php

namespace App\Controller;

use App\Entity\StatusRecord;
use App\Entity\ZonaCama;
use App\Form\ZonaCamaType;
use App\Repository\StatusRecordRepository;
use App\Repository\ZonaCamaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/zona/cama')]
final class ZonaCamaController extends AbstractController
{
    #[Route(name: 'app_zona_cama_index', methods: ['GET'])]
    public function index(ZonaCamaRepository $zonaCamaRepository): Response
    {
        return $this->render('zona_cama/index.html.twig', [
            'entities' => $zonaCamaRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_zona_cama_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $zonaCama = new ZonaCama();
        $form = $this->createForm(ZonaCamaType::class, $zonaCama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($zonaCama);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('zona_cama/new.html.twig', [
            'entity' => $zonaCama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_zona_cama_show', methods: ['GET'])]
    public function show(ZonaCama $zonaCama, StatusRecordRepository $statusRecordRepository): Response
    {
        if ($zonaCama->getStatus() != $statusRecordRepository->getActive()){
            $this->addFlash('error', 'No se pudo encontrar la informacion.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        return $this->render('zona_cama/show.html.twig', [
            'entity' => $zonaCama,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_zona_cama_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ZonaCama $zonaCama, EntityManagerInterface $entityManager): Response
    {
        if ($zonaCama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'No se pudo encontrar la informacion.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ZonaCamaType::class, $zonaCama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('zona_cama/edit.html.twig', [
            'entity' => $zonaCama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_zona_cama_delete', methods: ['POST'])]
    public function delete(Request $request, ZonaCama $zonaCama, EntityManagerInterface $entityManager): Response
    {
        if ($zonaCama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($zonaCama->getCamas()->count() > 0){
            $this->addFlash('error', 'Hay camas asignadas a esta zona, reasigne las camas para poder eliminar esta zona.');
            return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_FORBIDDEN);
        }

        $zonaCama->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
        $entityManager->persist($zonaCama);

        $entityManager->flush();

        $this->addFlash('success', 'La zona ha sido eliminada.');
        return $this->redirectToRoute('app_zona_cama_index', [], Response::HTTP_SEE_OTHER);
    }
}
