<?php

namespace App\Controller;

use App\Entity\HospitalizacionCama;
use App\Entity\StatusRecord;
use App\Enum\CamaEstados;
use App\Form\CamaHospitalizacionType;
use App\Repository\CamaHospitalizacionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hospitalizacioncama')]
final class CamaHospitalizacionController extends AbstractController
{
    #[Route(name: 'app_hospitalizacioncama_index', methods: ['GET'])]
    public function index(CamaHospitalizacionRepository $camaRepository): Response
    {
        return $this->render('cama_hospitalizacion/index.html.twig', [
            'entities' => $camaRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_hospitalizacioncama_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cama = new HospitalizacionCama();
        $form = $this->createForm(CamaHospitalizacionType::class, $cama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cama->setEstado(CamaEstados::AVAILABLE);
            $entityManager->persist($cama);
            $entityManager->flush();

            $this->addFlash('success', 'Registro creado');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cama_hospitalizacion/new.html.twig', [
            'entity' => $cama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hospitalizacioncama_show', methods: ['GET'])]
    public function show(HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('error', 'Informacion no encontrada');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        return $this->render('cama_hospitalizacion/show.html.twig', [
            'cama' => $cama,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hospitalizacioncama_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()) {
            $this->addFlash('error', 'Informacion no encontrada');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CamaHospitalizacionType::class, $cama);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro actualizado');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cama_hospitalizacion/edit.html.twig', [
            'entity' => $cama,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/maintenance', name: 'app_hospitalizacioncama_maintenance', methods: ['POST'])]
    public function maintenance(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() == CamaEstados::OCUPIED){
            $this->addFlash('error', 'No se puede mandar la cama a mantenimiento porque esta ocupada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::MAINTENANCE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido mandada a mantenimiento.');
        return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cleaning', name: 'app_hospitalizacioncama_cleaning', methods: ['POST'])]
    public function cleaning(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() == CamaEstados::OCUPIED){
            $this->addFlash('error', 'No se puede mandar la cama a limpiar porque esta ocupada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::CLEANING);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido mandada a limpiar.');
        return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/finish-cleaning', name: 'app_hospitalizacioncama_finish_cleaning', methods: ['POST'])]
    public function finishClening(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        if ($cama->getEstado() != CamaEstados::CLEANING){
            $this->addFlash('error', 'No se puede mandar la cama a limpiar porque esta ocupada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::AVAILABLE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ya esta limpia y puede ser usada en emergencias.');
        return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/reactivate', name: 'app_hospitalizacioncama_reactivate', methods: ['POST'])]
    public function reactivate(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($cama->getStatus() != $entityManager->getRepository(StatusRecord::class)->getActive()){
            $this->addFlash('error', 'Informacion no encontrada.');
            return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_NOT_FOUND);
        }

        $cama->setEstado(CamaEstados::AVAILABLE);
        $entityManager->persist($cama);

        $entityManager->flush();

        $this->addFlash('success', 'La cama ha sido reactivada exitosamente.');
        return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_hospitalizacioncama_delete', methods: ['POST'])]
    public function delete(Request $request, HospitalizacionCama $cama, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cama->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cama);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hospitalizacioncama_index', [], Response::HTTP_SEE_OTHER);
    }
}
