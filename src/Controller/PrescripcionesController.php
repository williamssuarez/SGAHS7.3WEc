<?php

namespace App\Controller;

use App\Entity\Consulta;
use App\Entity\Prescripciones;
use App\Form\PrescripcionesType;
use App\Repository\PrescripcionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/prescripciones')]
final class PrescripcionesController extends AbstractController
{
    #[Route(name: 'app_prescripciones_index', methods: ['GET'])]
    public function index(PrescripcionesRepository $prescripcionesRepository): Response
    {
        return $this->render('prescripciones/index.html.twig', [
            'prescripciones' => $prescripcionesRepository->findAll(),
        ]);
    }

    #[Route('/{id}/new-consulta', name: 'app_prescripciones_new_consulta', methods: ['GET', 'POST'])]
    public function newConsulta(Request $request, EntityManagerInterface $entityManager, Consulta $consulta): Response
    {
        $prescripcione = new Prescripciones();
        $form = $this->createForm(PrescripcionesType::class, $prescripcione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prescripcione->setPaciente($consulta->getPaciente());
            $entityManager->persist($prescripcione);
            $entityManager->flush();

            $this->addFlash('success', 'Prescripcion Agregada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prescripciones/newConsulta.html.twig', [
            'prescripcione' => $prescripcione,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_prescripciones_show', methods: ['GET'])]
    public function show(Prescripciones $prescripcione): Response
    {
        return $this->render('prescripciones/show.html.twig', [
            'prescripcione' => $prescripcione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_prescripciones_edit_consulta', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prescripciones $prescripcione, EntityManagerInterface $entityManager, Consulta $consulta): Response
    {
        $form = $this->createForm(PrescripcionesType::class, $prescripcione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Prescripcion Editada');
            return $this->redirectToRoute('app_consulta_show', ['id' => $consulta->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('prescripciones/editConsulta.html.twig', [
            'prescripcione' => $prescripcione,
            'form' => $form,
            'consultum' => $consulta,
        ]);
    }

    #[Route('/{id}', name: 'app_prescripciones_delete', methods: ['POST'])]
    public function delete(Request $request, Prescripciones $prescripcione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prescripcione->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prescripcione);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_prescripciones_index', [], Response::HTTP_SEE_OTHER);
    }
}
