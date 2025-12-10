<?php

namespace App\Controller;

use App\Entity\Alergias;
use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use App\Form\AlergiasType;
use App\Repository\AlergiasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'entities' => $alergiasRepository->getActivesforTable(),
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

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/new.html.twig', [
            'entity' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_show', methods: ['GET'])]
    public function show(Alergias $alergias, EntityManagerInterface $entityManager): Response
    {
        $alergia = $entityManager->getRepository(Alergias::class)->findOneBy([
            'id' => $alergias->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$alergia) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/show.html.twig', [
            'entity' => $alergia,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alergias_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alergias $alergias, EntityManagerInterface $entityManager): Response
    {
        $alergia = $entityManager->getRepository(Alergias::class)->findOneBy([
            'id' => $alergias->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$alergia) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AlergiasType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_alergias_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergias/edit.html.twig', [
            'entity' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergias_delete', methods: ['POST'])]
    public function delete(Request $request, Alergias $alergia, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $alergia->getId(), $submittedToken)) {
            $alergia->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($alergia);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
