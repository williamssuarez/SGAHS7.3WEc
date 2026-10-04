<?php

namespace App\Controller;

use App\Entity\Audit;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Enum\AuditTipos;
use App\Enum\CitasEstados;
use App\Repository\AuditRepository;
use App\Repository\CitasRepository;
use App\Repository\UserRepository;
use Composer\XdebugHandler\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auditoria')]
final class AuditoriaController extends AbstractController
{
    #[Route(name: 'app_auditoria_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, \Omines\DataTablesBundle\DataTableFactory $dataTableFactory): Response
    {
        $userRepository = $entityManager->getRepository(User::class);

        // Default values: today and 'expected' state
        $today = new \DateTime('now');

        $startDate = $request->query->get('startDate')
            ? new \DateTime($request->query->get('startDate'))
            : clone $today->setTime(0, 0, 0);

        $endDate = $request->query->get('endDate')
            ? new \DateTime($request->query->get('endDate'))
            : clone $today->setTime(23, 59, 59);

        $state = $request->query->get('state', AuditTipos::ALL->value);
        $userId = $request->query->get('user', null);

        $table = $dataTableFactory->createFromType(\App\DataTable\Type\AuditoriaTableType::class, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'state' => $state,
            'userId' => $userId,
        ], ['pageLength' => 5])->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $usuarios = $userRepository->findBy([
            'status' => $entityManager->getRepository(StatusRecord::class)->getActive(),
        ]);

        $tipos = AuditTipos::cases();

        return $this->render('auditoria/index.html.twig', [
            'datatable' => $table,
            'currentState' => $state,
            'currentUser' => $userId,
            'usuarios' => $usuarios,
            'tipos' => $tipos,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }
}

