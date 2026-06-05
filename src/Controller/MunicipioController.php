<?php

namespace App\Controller;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Form\MunicipioType;
use App\Repository\MunicipioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/municipio')]
final class MunicipioController extends AbstractController
{
    #[Route(name: 'app_municipio_index', methods: ['GET'])]
    public function index(MunicipioRepository $municipioRepository): Response
    {
        return $this->render('municipio/index.html.twig', [
            'entities' => $municipioRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_municipio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $municipio = new Municipio();
        $form = $this->createForm(MunicipioType::class, $municipio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($municipio);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_municipio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('municipio/new.html.twig', [
            'municipio' => $municipio,
            'form' => $form,
        ]);
    }

    #[Route('/fromestado/{id}/new', name: 'app_municipio_new_from_estado', methods: ['GET', 'POST'])]
    public function newFromEstado(Request $request, Estado $estado, EntityManagerInterface $entityManager): Response
    {
        $municipio = new Municipio();
        $form = $this->createForm(MunicipioType::class, $municipio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $municipio->setEstado($estado);
            $entityManager->persist($municipio);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_municipio_show', ['id' => $municipio->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('municipio/new.html.twig', [
            'municipio' => $municipio,
            'estado' => $estado,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_municipio_show', methods: ['GET'])]
    public function show(Municipio $municipio): Response
    {
        return $this->render('municipio/show.html.twig', [
            'entity' => $municipio,
            'estado' => $municipio->getEstado(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_municipio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Municipio $municipio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MunicipioType::class, $municipio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_municipio_show', ['id' => $municipio->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('municipio/edit.html.twig', [
            'municipio' => $municipio,
            'estado' => $municipio->getEstado(),
            'form' => $form,
        ]);
    }
}
