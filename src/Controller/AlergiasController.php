<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Form\AlergiasType;
use App\Repository\AlergiasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alergias')]
final class AlergiasController extends AbstractController
{
    #[Route(name: 'app_alergias_index', methods: ['GET'])]
    public function index(AlergiasRepository $alergiasRepository): Response
    {
        return $this->render('alergias/index.html.twig', [
            'alergias' => $alergiasRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_alergias_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alergia = new Alergias();
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alergia);
            $entityManager->flush();

            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/new.html.twig', [
            'alergia' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_show', methods: ['GET'])]
    public function show(Alergias $alergia): Response
    {
        return $this->render('alergias/show.html.twig', [
            'alergia' => $alergia,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alergias_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alergias $alergia, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/edit.html.twig', [
            'alergia' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_delete', methods: ['POST'])]
    public function delete(Request $request, Alergias $alergia, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alergia->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alergia);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
    }
}
