<?php

namespace App\Controller;

use App\Enum\CitasEstados;
use App\Repository\CitasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/citas/estadisticas')]
final class CitasEstadisticasController extends AbstractController
{
    #[Route('/listado', name: 'app_citas_estadisticas_list', methods: ['GET'])]
    public function listado(Request $request, CitasRepository $citasRepository): Response
    {
        // Default values: today and 'expected' state
        $today = new \DateTime('now');

        $startDate = $request->query->get('startDate')
            ? new \DateTime($request->query->get('startDate'))
            : clone $today->setTime(0, 0, 0);

        $endDate = $request->query->get('endDate')
            ? new \DateTime($request->query->get('endDate'))
            : clone $today->setTime(23, 59, 59);

        $state = $request->query->get('state', CitasEstados::EXPECTED->value);

        if ($state == 'all'){
            $entities = $citasRepository->getActivesforTableByDateOnly($startDate, $endDate);
            $statesConfig = [
                'expected' => ['label' => 'Pendientes', 'color' => '#ffc107'], // Yellow
                'checked_in' => ['label' => 'En Espera', 'color' => '#17a2b8'], // Blue
                'finalized' => ['label' => 'Finalizadas', 'color' => '#28a745'], // Green
                'canceled' => ['label' => 'Canceladas', 'color' => '#dc3545'], // Red
            ];

            $dbData = $citasRepository->countAppointmentsByDayAndState($startDate, $endDate);

            // 1. Create a 2D map: $dataMap['16/03']['expected'] = 5
            $dataMap = [];
            foreach ($dbData as $row) {
                $dateKey = $row['fecha']->format('d/m');
                $stateKey = $row['estadoCita'] instanceof \BackedEnum ? $row['estadoCita']->value : $row['estadoCita'];
                $dataMap[$dateKey][$stateKey] = (int)$row['total'];
            }

            // 2. Prepare the period and empty datasets
            $labels = [];
            $datasets = [];
            foreach ($statesConfig as $key => $config) {
                $datasets[$key] = [
                    'label' => $config['label'],
                    'borderColor' => $config['color'],
                    'backgroundColor' => $config['color'],
                    'data' => [],
                ];
            }

            $period = new \DatePeriod($startDate, new \DateInterval('P1D'), (clone $endDate)->modify('+1 day'));

            // 3. Fill gaps for every day and every state
            foreach ($period as $date) {
                $dateKey = $date->format('d/m');
                $labels[] = $dateKey;

                foreach ($statesConfig as $stateKey => $config) {
                    $datasets[$stateKey]['data'][] = $dataMap[$dateKey][$stateKey] ?? 0;
                }
            }

            $chartData = [
                'labels' => $labels,
                'datasets' => array_values($datasets), // Chart.js expects a numeric array of objects
            ];

            $cardData = $citasRepository->getSummaryCountsByState($startDate, $endDate);
        } else {
            $entities = $citasRepository->getActivesforTableByState($state, $startDate, $endDate);
            $chartData = null;
            $cardData = null;
        }

        return $this->render('citas_estadisticas/index.html.twig', [
            'entities' => $entities,
            'currentState' => $state,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'chartData' => $chartData,
            'cardData' => $cardData,
        ]);
    }
}
