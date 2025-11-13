<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Form\EmpleadoType;
use App\Repository\EmpleadoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/empleado')]
final class EmpleadoController extends AbstractController
{
    #[Route(name: 'app_empleado_index', methods: ['GET'])]
    public function index(EmpleadoRepository $empleadoRepository): Response
    {
        return $this->render('empleado/index.html.twig', [
            'empleados' => $empleadoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_empleado_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $empleado = new Empleado();
        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($empleado);
            $entityManager->flush();

            return $this->redirectToRoute('app_empleado_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('empleado/new.html.twig', [
            'empleado' => $empleado,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_empleado_show', methods: ['GET'])]
    public function show(Empleado $empleado): Response
    {
        return $this->render('empleado/show.html.twig', [
            'empleado' => $empleado,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_empleado_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Empleado $empleado, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_empleado_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('empleado/edit.html.twig', [
            'empleado' => $empleado,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_empleado_delete', methods: ['POST'])]
    public function delete(Request $request, Empleado $empleado, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$empleado->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($empleado);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_empleado_index', [], Response::HTTP_SEE_OTHER);
    }
}
