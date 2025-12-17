<?php

namespace App\Controller;

use App\Entity\Alergenos;
use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use App\Form\AlergenosType;
use App\Repository\AlergenosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alergenos')]
final class AlergenosController extends AbstractController
{
    #[Route(name: 'app_alergenos_index', methods: ['GET'])]
    public function index(AlergenosRepository $alergenosRepository): Response
    {
        return $this->render('alergenos/index.html.twig', [
            'entities' => $alergenosRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_alergenos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alergia = new Alergenos();
        $form = $this->createForm(AlergenosType::class, $alergia);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alergia);
            $entityManager->flush();

            $this->addFlash('success', 'Registro Agregado.');
            return $this->redirectToRoute('app_alergenos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergenos/new.html.twig', [
            'entity' => $alergia,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergenos_show', methods: ['GET'])]
    public function show(Alergenos $alergenos, EntityManagerInterface $entityManager): Response
    {
        $alergia = $entityManager->getRepository(Alergenos::class)->findOneBy([
            'id' => $alergenos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$alergia) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_alergenos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergenos/show.html.twig', [
            'entity' => $alergia,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alergias_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alergenos $alergenos, EntityManagerInterface $entityManager): Response
    {
        $alergeno = $entityManager->getRepository(Alergenos::class)->findOneBy([
            'id' => $alergenos->getId(),
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive()
        ]);

        if (!$alergeno) {
            $this->addFlash('danger', 'Informacion no encontrada');
            return $this->redirectToRoute('app_alergenos_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AlergenosType::class, $alergeno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Registro Editado.');
            return $this->redirectToRoute('app_alergenos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alergenos/edit.html.twig', [
            'entity' => $alergeno,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alergenos_delete', methods: ['POST'])]
    public function delete(Request $request, Alergenos $alergenos, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $alergenos->getId(), $submittedToken)) {
            $alergenos->setStatus($entityManager->getRepository(StatusRecord::class)->getRemove());
            $entityManager->persist($alergenos);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
    }
}
