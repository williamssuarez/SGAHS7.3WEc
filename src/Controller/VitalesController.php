<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\Vitales;
use App\Form\VitalesType;
use App\Repository\StatusRecordRepository;
use App\Repository\VitalesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vitales')]
final class VitalesController extends AbstractController
{
    #[Route(name: 'app_vitales_index', methods: ['GET'])]
    public function index(VitalesRepository $vitalesRepository): Response
    {
        return $this->render('vitales/index.html.twig', [
            'vitales' => $vitalesRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new-consulta', name: 'app_vitales_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta, StatusRecordRepository $statusRecordRepository): Response
    {
        $vitale = new Vitales();
        $form = $this->createForm(VitalesType::class, $vitale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vitale);
            $entityManager->flush();

            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vitales/new.html.twig', [
            'vitale' => $vitale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vitales_show', methods: ['GET'])]
    public function show(Vitales $vitale): Response
    {
        return $this->render('vitales/show.html.twig', [
            'vitale' => $vitale,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vitales_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vitales $vitale, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VitalesType::class, $vitale);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vitales_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vitales/edit.html.twig', [
            'vitale' => $vitale,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vitales_delete', methods: ['POST'])]
    public function delete(Request $request, Vitales $vitale, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vitale->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vitale);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vitales_index', [], Response::HTTP_SEE_OTHER);
    }
}
