<?php

namespace App\Controller;

use App\Entity\Enfermedades;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Exception\BusinessRuleException;
use App\Form\UserType;
use App\Repository\StatusRecordRepository;
use App\Repository\UserRepository;
use App\Service\InternalProfileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user_internal')]
final class UserInternalController extends AbstractController
{
    #[Route(name: 'app_user_internal_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('users/user_internal/index.html.twig', [
            'users' => $userRepository->getActivesInternalsforTable(),
        ]);
    }

    #[Route('/new', name: 'app_user_internal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, InternalProfileProcessor $internalProfileProcessor): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // dejar que el servicio procese el form
                $internalProfileProcessor->processFormSubmission($user->getInternalProfile(), $form->get('avatarUrl')->getData());

                $this->addFlash('success', 'Registro Agregado.');
                return $this->redirectToRoute('app_user_internal_index', [], Response::HTTP_SEE_OTHER);

            } catch (BusinessRuleException $e) {
                //Obtener el mensaje especifico y mostrar el error
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('users/user_internal/new.html.twig', [
            'user_internal' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_internal_show', methods: ['GET'])]
    public function show(User $user, StatusRecordRepository $recordRepository): Response
    {
        if ($user->getStatus() != $recordRepository->getActive()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_internal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/user_internal/show.html.twig', [
            'user_internal' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_internal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository): Response
    {
        if ($user->getStatus() != $recordRepository->getActive()){
            $this->addFlash('error', 'No se pudo encontrar el registro.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_internal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/user_internal/edit.html.twig', [
            'user_internal' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_internal_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, StatusRecordRepository $recordRepository): Response
    {
        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $submittedToken)) {
            $user->setStatus($recordRepository->getRemove());
            $entityManager->persist($user);
            $entityManager->flush();
        } else {
            return new JsonResponse('Token Invalido', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse('Eliminado con exito', Response::HTTP_OK);
        //return $this->redirectToRoute('app_enfermedades_index', [], Response::HTTP_SEE_OTHER);
    }
}
