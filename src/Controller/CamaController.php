<?php

namespace App\Controller;

use App\Entity\Cama;
use App\Entity\StatusRecord;
use App\Entity\ZonaCama;
use App\Enum\CamaEstados;
use App\Form\CamaType;
use App\Repository\CamaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cama')]
final class CamaController extends AbstractController
{
    #[Route(name: 'app_cama_index', methods: ['GET'])]
    public function index(CamaRepository $camaRepository): Response
    {
        return $this->render('cama/index.html.twig', [
            'entities' => $camaRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_cama_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cama = new Cama();
        $form = $this->createForm(CamaType::class, $cama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cama->setEstado(CamaEstados::AVAILABLE);
            $entityManager->persist($cama);
            $entityManager->flush();

            $this->addFlash('success', 'Registro creado');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cama/new.html.twig', [
            'entity' => $cama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cama_show', methods: ['GET'])]
    public function show(Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('error', 'Informacion no encontrada');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        return $this->render('cama/show.html.twig', [
            'cama' => $cama,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cama_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('error', 'Informacion no encontrada');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CamaType::class, $cama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro actualizado');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cama/edit.html.twig', [
            'entity' => $cama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/maintenance', name: 'app_cama_maintenance', methods: ['POST'])]
    public function maintenance(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() == CamaEstados::OCUPIED){
            $this->addFlash('error', 'No se puede mandar la cama a mantenimiento porque esta ocupada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::MAINTENANCE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido mandada a mantenimiento.');
        return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cleaning', name: 'app_cama_cleaning', methods: ['POST'])]
    public function cleaning(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() == CamaEstados::OCUPIED){
            $this->addFlash('error', 'No se puede mandar la cama a limpiar porque esta ocupada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::CLEANING);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido mandada a limpiar.');
        return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/finish-cleaning', name: 'app_cama_finish_cleaning', methods: ['POST'])]
    public function finishClening(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() != CamaEstados::CLEANING){
            $this->addFlash('error', 'No se puede mandar la cama a limpiar porque esta ocupada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::AVAILABLE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ya esta limpia y puede ser usada en emergencias.');
        return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/reactivate', name: 'app_cama_reactivate', methods: ['POST'])]
    public function reactivate(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_cama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::AVAILABLE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido reactivada exitosamente.');
        return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_cama_delete', methods: ['POST'])]
    public function delete(Request $request, Cama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cama->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cama);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cama_index', [], Response::HTTP_SEE_OTHER);
    }
}
