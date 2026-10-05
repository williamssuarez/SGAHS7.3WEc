<?php

namespace App\Controller;

use App\Entity\Audit;
use App\Entity\StatusRecord;
use App\Entity\User;
use App\Enum\AuditTipos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Omines\DataTablesBundle\DataTableFactory;

#[Route('/auditoria')]
final class AuditoriaController extends AbstractController
{
    #[Route(name: 'app_auditoria_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, DataTableFactory $dataTableFactory): Response
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

        // Delegate to private method if PDF export is requested
        if ($request->query->get('export') === 'pdf') {
            return $this->generatePdfReport($request, $entityManager, $startDate, $endDate, $state, $userId);
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

    private function generatePdfReport(Request $request, EntityManagerInterface $entityManager, \DateTime $startDate, \DateTime $endDate, string $state, ?string $userId): Response
    {
        $audits = [];
        if ($state === 'all') {
            $audits = $entityManager->getRepository(Audit::class)->getActivesforTableByDateOnly($startDate, $endDate, $userId);
        } else {
            $audits = $entityManager->getRepository(Audit::class)->getActivesforTableByState($state, $startDate, $endDate, $userId);
        }

        if (count($audits) > 1000) {
            $this->addFlash('danger', sprintf('Demasiados registros para exportar (%d). El límite es 1000. Por favor, ajuste los filtros.', count($audits)));
            // Remove export param to prevent infinite loop of redirects
            $params = $request->query->all();
            unset($params['export']);
            return $this->redirectToRoute('app_auditoria_index', $params);
        }

        if (count($audits) === 0) {
            $this->addFlash('warning', 'No hay registros para exportar con los filtros actuales.');
            $params = $request->query->all();
            unset($params['export']);
            return $this->redirectToRoute('app_auditoria_index', $params);
        }

        $html = $this->renderView('auditoria/pdf_report.html.twig', [
            'audits' => $audits,
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'state' => $state,
            'generatedBy' => $this->getUser(),
            'generationDate' => new \DateTime('now', new \DateTimeZone('America/Caracas')),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="reporte_auditoria.pdf"'
            ]
        );
    }
}
