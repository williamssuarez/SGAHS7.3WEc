<?php

namespace App\Controller;

use App\Entity\CitasConfiguraciones;
use App\Form\CitasConfiguracionesType;
use App\Repository\CitasConfiguracionesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citas/configuraciones')]
final class CitasConfiguracionesController extends AbstractController
{
    #[Route(name: 'app_citas_configuraciones_index', methods: ['GET'])]
    public function index(CitasConfiguracionesRepository $citasConfiguracionesRepository): Response
    {
        return $this->render('citas_configuraciones/index.html.twig', [
            'entities' => $citasConfiguracionesRepository->getActivesforTable(),
        ]);
    }

    #[Route('/new', name: 'app_citas_configuraciones_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $citasConfiguracione = new CitasConfiguraciones();
        $form = $this->createForm(CitasConfiguracionesType::class, $citasConfiguracione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($citasConfiguracione);
            $entityManager->flush();

            $this->addFlash('success', 'Configuracion Establecida.');
            return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_configuraciones/new.html.twig', [
            'entity' => $citasConfiguracione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_citas_configuraciones_show', methods: ['GET'])]
    public function show(CitasConfiguraciones $citasConfiguracione): Response
    {
        return $this->render('citas_configuraciones/show.html.twig', [
            'citas_configuracione' => $citasConfiguracione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_citas_configuraciones_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CitasConfiguraciones $citasConfiguracione, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CitasConfiguracionesType::class, $citasConfiguracione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Configuracion Establecida.');
            return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('citas_configuraciones/edit.html.twig', [
            'entity' => $citasConfiguracione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_citas_configuraciones_delete', methods: ['POST'])]
    public function delete(Request $request, CitasConfiguraciones $citasConfiguracione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$citasConfiguracione->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($citasConfiguracione);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_citas_configuraciones_index', [], Response::HTTP_SEE_OTHER);
    }
}
