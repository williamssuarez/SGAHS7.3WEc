<?php

namespace App\Controller;

use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Entity\Sector;
use App\Form\ParroquiaType;
use App\Form\SectorType;
use App\Repository\SectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sector')]
final class SectorController extends AbstractController
{
    #[Route(name: 'app_sector_index', methods: ['GET'])]
    public function index(SectorRepository $sectorRepository): Response
    {
        return $this->render('sector/index.html.twig', [
            'sectors' => $sectorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sector_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sector = new Sector();
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sector);
            $entityManager->flush();

            return $this->redirectToRoute('app_sector_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sector/new.html.twig', [
            'sector' => $sector,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sector_show', methods: ['GET'])]
    public function show(Sector $sector): Response
    {
        return $this->render('sector/show.html.twig', [
            'sector' => $sector,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sector_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sector $sector, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_parroquia_show', ['id' => $sector->getParroquia()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sector/edit.html.twig', [
            'sector' => $sector,
            'municipio' => $sector->getParroquia()->getMunicipio(),
            'parroquia' => $sector->getParroquia(),
            'form' => $form,
        ]);
    }

    #[Route('/fromparroquia/{id}/new', name: 'app_sector_new_from_parroquia', methods: ['GET', 'POST'])]
    public function newFromParroquia(Request $request, Parroquia $parroquia, EntityManagerInterface $entityManager): Response
    {
        $sector = new Sector();
        $form = $this->createForm(SectorType::class, $sector);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sector->setParroquia($parroquia);
            $entityManager->persist($sector);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_parroquia_show', ['id' => $parroquia->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sector/new.html.twig', [
            'municipio' => $parroquia->getMunicipio(),
            'form' => $form,
            'parroquia' => $parroquia,
        ]);
    }
}
