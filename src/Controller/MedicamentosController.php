<?php

namespace App\Controller;

use App\Entity\Medicamentos;
use App\Entity\StatusRecord;
use App\Form\MedicamentosType;
use App\Repository\MedicamentosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/medicamentos')]
final class MedicamentosController extends AbstractController
{
    #[Route(name: 'app_medicamentos_index', methods: ['GET'])]
    public function index(MedicamentosRepository $medicamentosRepository): Response
    {
        return $this->render('medicamentos/index.html.twig', [
            'entities' => $medicamentosRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_medicamentos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $medicamento = new Medicamentos();
        $form = $this->createForm(MedicamentosType::class, $medicamento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($medicamento);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicamentos/new.html.twig', [
            'entity' => $medicamento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicamentos_show', methods: ['GET'])]
    public function show(Medicamentos $medicamentos, EntityManagerInterface $entityManager): Response
    {
        $medicamento = $entityManager->getRepository(Medicamentos::class)->findOneBy([
            'id' => $medicamentos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$medicamento) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicamentos/show.html.twig', [
            'entity' => $medicamento,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medicamentos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medicamentos $medicamentos, EntityManagerInterface $entityManager): Response
    {
        $medicamento = $entityManager->getRepository(Medicamentos::class)->findOneBy([
            'id' => $medicamentos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$medicamento) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(MedicamentosType::class, $medicamento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_medicamentos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('medicamentos/edit.html.twig', [
            'medicamento' => $medicamento,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medicamentos_delete', methods: ['POST'])]
    public function delete(Request $request, Medicamentos $medicamento, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $medicamento->getId(), $submittedToken)) {
            $medicamento->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($medicamento);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
