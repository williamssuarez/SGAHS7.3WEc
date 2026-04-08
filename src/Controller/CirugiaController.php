<?php

namespace App\Controller;

use App\Entity\Cirugia;
use App\Enum\CirugiaEstados;
use App\Form\CirugiaType;
use App\Repository\CirugiaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cirugia')]
final class CirugiaController extends AbstractController
{
    #[Route(name: 'app_cirugia_index', methods: ['GET'])]
    public function index(CirugiaRepository $cirugiaRepository): Response
    {
        return $this->render('cirugia/index.html.twig', [
            'entities' => $cirugiaRepository->findBy([
                'estado' => CirugiaEstados::PROGRAMADA->value
            ]),
        ]);
    }

    #[Route('/new', name: 'app_cirugia_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cirugium = new Cirugia();
        $form = $this->createForm(CirugiaType::class, $cirugium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cirugium->setEstado(CirugiaEstados::PROGRAMADA->value);
            $entityManager->persist($cirugium);
            $entityManager->flush();

            return $this->redirectToRoute('app_cirugia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cirugia/new.html.twig', [
            'cirugium' => $cirugium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cirugia_show', methods: ['GET'])]
    public function show(Cirugia $cirugium): Response
    {
        return $this->render('cirugia/show.html.twig', [
            'cirugium' => $cirugium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cirugia_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cirugia $cirugium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CirugiaType::class, $cirugium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cirugia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cirugia/edit.html.twig', [
            'cirugium' => $cirugium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cirugia_delete', methods: ['POST'])]
    public function delete(Request $request, Cirugia $cirugium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cirugium->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cirugium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cirugia_index', [], Response::HTTP_SEE_OTHER);
    }
}
