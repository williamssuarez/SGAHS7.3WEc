<?php

namespace App\Controller;

use App\Enum\CitasEstados;
use App\Repository\CitasRepository;
use App\Repository\CitasSolicitudesRepository;
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

    #[Route('/especialidades', name: 'app_citas_estadisticas_especialidades', methods: ['GET'])]
    public function especialidadesStats(
        Request $request,
        CitasRepository $citasRepository,
        CitasSolicitudesRepository $solicitudesRepository
    ): Response {
        $today = new \DateTime('now');

        $startDate = $request->query->get('startDate')
            ? new \DateTime($request->query->get('startDate'))
            : clone $today->setTime(0, 0, 0);

        $endDate = $request->query->get('endDate')
            ? new \DateTime($request->query->get('endDate'))
            : clone $today->setTime(23, 59, 59);

        // 1. Fetch the raw data from the repositories
        $requestsData = $solicitudesRepository->countRequestsBySpecialty($startDate, $endDate);
        $assignedData = $citasRepository->countAssignedBySpecialty($startDate, $endDate);

        // 2. Build the Unified Dictionary
        $dataMap = [];

        // Map the Requests (Demand)
        foreach ($requestsData as $row) {
            $name = $row['specialtyName'];
            if (!isset($dataMap[$name])) {
                $dataMap[$name] = ['requests' => 0, 'assigned' => 0];
            }
            $dataMap[$name]['requests'] = (int) $row['totalRequests'];
        }

        // Map the Assigned Appointments (Capacity)
        foreach ($assignedData as $row) {
            $name = $row['specialtyName'];
            if (!isset($dataMap[$name])) {
                $dataMap[$name] = ['requests' => 0, 'assigned' => 0];
            }
            $dataMap[$name]['assigned'] = (int) $row['totalAssigned'];
        }

        // 3. Sort the array so the most demanded specialties appear first (on the left of the chart)
        uasort($dataMap, function($a, $b) {
            return $b['requests'] <=> $a['requests']; // Descending order based on requests
        });

        // 4. Extract into Chart.js format
        $labels = array_keys($dataMap);
        $requestsArray = array_column($dataMap, 'requests');
        $assignedArray = array_column($dataMap, 'assigned');

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Solicitudes (Demanda)',
                    'backgroundColor' => '#0d6efd', // Primary Blue
                    'data' => $requestsArray
                ],
                [
                    'label' => 'Citas Asignadas (Capacidad)',
                    'backgroundColor' => '#198754', // Success Green
                    'data' => $assignedArray
                ]
            ]
        ];

        return $this->render('citas_estadisticas/especialidades.html.twig', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'chartData' => $chartData,
            'entities' => null
        ]);
    }
}
