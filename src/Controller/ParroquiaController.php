<?php

namespace App\Controller;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Form\MunicipioType;
use App\Form\ParroquiaType;
use App\Repository\ParroquiaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parroquia')]
final class ParroquiaController extends AbstractController
{
    #[Route(name: 'app_parroquia_index', methods: ['GET'])]
    public function index(ParroquiaRepository $parroquiaRepository): Response
    {
        return $this->render('parroquia/index.html.twig', [
            'parroquias' => $parroquiaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_parroquia_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parroquium = new Parroquia();
        $form = $this->createForm(ParroquiaType::class, $parroquium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($parroquium);
            $entityManager->flush();

            return $this->redirectToRoute('app_parroquia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parroquia/new.html.twig', [
            'parroquium' => $parroquium,
            'form' => $form,
        ]);
    }

    #[Route('/frommunicipio/{id}/new', name: 'app_parroquia_new_from_municipio', methods: ['GET', 'POST'])]
    public function newFromMunicipio(Request $request, Municipio $municipio, EntityManagerInterface $entityManager): Response
    {
        $parroquia = new Parroquia();
        $form = $this->createForm(ParroquiaType::class, $parroquia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parroquia->setMunicipio($municipio);
            $entityManager->persist($parroquia);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_parroquia_show', ['id' => $parroquia->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parroquia/new.html.twig', [
            'municipio' => $municipio,
            'estado' => $municipio->getEstado(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parroquia_show', methods: ['GET'])]
    public function show(Parroquia $parroquium): Response
    {
        return $this->render('parroquia/show.html.twig', [
            'entity' => $parroquium,
            'estado' => $parroquium->getMunicipio()->getEstado(),
            'municipio' => $parroquium->getMunicipio(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parroquia_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Parroquia $parroquium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParroquiaType::class, $parroquium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_parroquia_show', ['id' => $parroquium->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parroquia/edit.html.twig', [
            'parroquia' => $parroquium,
            'municipio' => $parroquium->getMunicipio(),
            'estado' => $parroquium->getMunicipio()->getEstado(),
            'form' => $form,
        ]);
    }
}
