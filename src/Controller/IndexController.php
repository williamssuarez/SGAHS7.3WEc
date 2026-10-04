<?php

namespace App\Controller;

use App\Entity\Cirugia;
use App\Entity\Citas;
use App\Entity\Consulta;
use App\Entity\Emergencia;
use App\Entity\Paciente;
use App\Enum\CirugiaEstados;
use App\Enum\CitasEstados;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 1. Dispatching basado en Roles
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->adminDashboard();
        }

        if ($this->isGranted('ROLE_ADMIN_QUIROFANO') || $this->isGranted('ROLE_OR')) {
            return $this->quirofanoDashboard();
        }

        if ($this->isGranted('ROLE_ER_DOCTOR') || $this->isGranted('ROLE_ER')) {
            return $this->emergenciaDashboard();
        }

        if ($this->isGranted('ROLE_INTERNAL')) {
            return $this->redirectToRoute('app_paciente_index');
        }

        // External Users / Pacientes
        return $this->redirectToRoute('app_profile_complete'); // We will change this later when we build the patient dashboard
    }

    private function adminDashboard(): Response
    {
        return $this->render('dashboard/admin.html.twig');
    }

    #[Route('/api/dashboard/admin', name: 'api_dashboard_admin', methods: ['GET'])]
    public function getAdminDashboardData(EntityManagerInterface $em): Response
    {
        // Require ADMIN role for this data
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $sevenDaysAgo = (new \DateTime('-7 days'))->setTime(0, 0, 0);

        // --- 1. Tarjetas de Totales (Mﾃｩtricas) ---
        // Total de pacientes
        $totalPacientes = $em->getRepository(Paciente::class)->count([]);

        // Citas de Hoy
        $citasHoy = $em->getRepository(Citas::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fecha >= :today')
            ->andWhere('c.fecha < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        // Emergencias Activas (Triaje, Observaciﾃｳn, etc)
        $emergenciasActivas = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.estado IN (:estadosActivos)')
            ->setParameter('estadosActivos', [
                \App\Enum\EmergenciasEstados::WAITING_TRIAGE,
                \App\Enum\EmergenciasEstados::WAITING_BED,
                \App\Enum\EmergenciasEstados::IN_TREATMENT
            ])
            ->getQuery()
            ->getSingleScalarResult();

        // Cirugﾃｭas Hoy
        $cirugiasHoy = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.fechaHoraProgramada >= :today')
            ->andWhere('c.fechaHoraProgramada < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        // --- 2. Datos para el Grﾃ｡fico de Torta (Citas Hoy por Estado) ---
        $citasPorEstadoRaw = $em->getRepository(Citas::class)->createQueryBuilder('c')
            ->select('c.estadoCita as estado, COUNT(c.id) as total')
            ->where('c.fecha >= :today')
            ->andWhere('c.fecha < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->groupBy('c.estadoCita')
            ->getQuery()
            ->getResult();

        $citasLabels = [];
        $citasData = [];
        $citasColors = [];
        $colorMap = [
            CitasEstados::EXPECTED->name => '#ffc107', // Warning
            CitasEstados::CHECKED_IN->name => '#17a2b8', // Info
            CitasEstados::COMPLETED->name => '#28a745', // Success
            CitasEstados::CANCELED->name => '#dc3545', // Danger
        ];

        foreach ($citasPorEstadoRaw as $row) {
            /** @var CitasEstados $estado */
            $estado = $row['estado'];
            $citasLabels[] = $estado->getReadableText();
            $citasData[] = $row['total'];
            $citasColors[] = $colorMap[$estado->name] ?? '#cccccc';
        }

        // --- 3. Datos para el Grﾃ｡fico de Barras (Atenciones en los ﾃｺltimos 7 dﾃｭas) ---
        // Generar array de los ﾃｺltimos 7 dﾃｭas
        $diasLabels = [];
        $citasSemanaData = array_fill(0, 7, 0);
        $consultasSemanaData = array_fill(0, 7, 0);
        $emergenciasSemanaData = array_fill(0, 7, 0);

        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-$i days");
            $diasLabels[] = $date->format('d/m');
        }

        // Consultas de los ﾃｺltimos 7 dﾃｭas
        $consultasRaw = $em->getRepository(Consulta::class)->createQueryBuilder('c')
            ->select('c.fechaInicio as fecha')
            ->where('c.fechaInicio >= :start')
            ->setParameter('start', $sevenDaysAgo)
            ->getQuery()
            ->getResult();

        foreach ($consultasRaw as $row) {
            /** @var \DateTime $fecha */
            $fecha = $row['fecha'];
            $diff = (new \DateTime('today'))->diff((clone $fecha)->setTime(0,0,0))->days;
            $index = 6 - $diff;
            if ($index >= 0 && $index <= 6) {
                $consultasSemanaData[$index]++;
            }
        }

        // Emergencias de los ﾃｺltimos 7 dﾃｭas
        $emergenciasRaw = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('e.fechaIngreso as fecha')
            ->where('e.fechaIngreso >= :start')
            ->setParameter('start', $sevenDaysAgo)
            ->getQuery()
            ->getResult();

        foreach ($emergenciasRaw as $row) {
            /** @var \DateTime $fecha */
            $fecha = $row['fecha'];
            $diff = (new \DateTime('today'))->diff((clone $fecha)->setTime(0,0,0))->days;
            $index = 6 - $diff;
            if ($index >= 0 && $index <= 6) {
                $emergenciasSemanaData[$index]++;
            }
        }

        // Citas de los ﾃｺltimos 7 dﾃｭas
        $citasRaw = $em->getRepository(Citas::class)->createQueryBuilder('c')
            ->select('c.fecha as fecha')
            ->where('c.fecha >= :start')
            ->setParameter('start', $sevenDaysAgo)
            ->getQuery()
            ->getResult();

        foreach ($citasRaw as $row) {
            /** @var \DateTime $fecha */
            $fecha = $row['fecha'];
            $diff = (new \DateTime('today'))->diff((clone $fecha)->setTime(0,0,0))->days;
            $index = 6 - $diff;
            if ($index >= 0 && $index <= 6) {
                $citasSemanaData[$index]++;
            }
        }

        return $this->json([
            'cards' => [
                'card1' => $totalPacientes,
                'card2' => $citasHoy,
                'card3' => $cirugiasHoy,
                'card4' => $emergenciasActivas,
            ],
            'charts' => [
                'bar' => [
                    'labels' => $diasLabels,
                    'datasets' => [
                        [
                            'label' => 'Citas',
                            'backgroundColor' => 'rgba(40, 167, 69, 0.2)',
                            'borderColor' => '#28a745',
                            'data' => $citasSemanaData,
                            'fill' => true,
                            'tension' => 0.4
                        ],
                        [
                            'label' => 'Consultas',
                            'backgroundColor' => 'rgba(0, 123, 255, 0.2)',
                            'borderColor' => '#007bff',
                            'data' => $consultasSemanaData,
                            'fill' => true,
                            'tension' => 0.4
                        ],
                        [
                            'label' => 'Emergencias',
                            'backgroundColor' => 'rgba(220, 53, 69, 0.2)',
                            'borderColor' => '#dc3545',
                            'data' => $emergenciasSemanaData,
                            'fill' => true,
                            'tension' => 0.4
                        ]
                    ]
                ],
                'pie' => [
                    'labels' => $citasLabels,
                    'datasets' => [
                        [
                            'data' => $citasData,
                            'backgroundColor' => $citasColors
                        ]
                    ],
                    'totalHoy' => $citasHoy
                ]
            ]
        ]);
    }
    private function quirofanoDashboard(): Response
    {
        return $this->render('dashboard/quirofano.html.twig');
    }

    #[Route('/api/dashboard/quirofano', name: 'api_dashboard_quirofano', methods: ['GET'])]
    public function getQuirofanoDashboardData(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_OR');

        // Card 1: Agendadas (Programadas)
        $agendadas = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.estado = :estado')
            ->setParameter('estado', \App\Enum\CirugiaEstados::PROGRAMADA)
            ->getQuery()
            ->getSingleScalarResult();

        // Card 2: En Proceso (Trans-operatorio o Pre-operatorio o en sala)
        $enProceso = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.estado IN (:estados)')
            ->setParameter('estados', [\App\Enum\CirugiaEstados::TRANS_OP, \App\Enum\CirugiaEstados::PRE_OP, \App\Enum\CirugiaEstados::EN_SALA])
            ->getQuery()
            ->getSingleScalarResult();

        // Card 3: Finalizadas (Hoy)
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $finalizadasHoy = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.estado IN (:estados)')
            ->andWhere('c.fechaHoraProgramada >= :today AND c.fechaHoraProgramada < :tomorrow')
            ->setParameter('estados', [\App\Enum\CirugiaEstados::FINALIZADA, \App\Enum\CirugiaEstados::POST_OP])
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        // Card 4: Quirófanos Totales
        $quirofanosTotales = $em->getRepository(\App\Entity\Quirofano::class)->count([]);

        // Chart 1 (Pie): Distribución de Cirugías Hoy
        $cirugiasHoyRaw = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('c.estado, COUNT(c.id) as total')
            ->where('c.fechaHoraProgramada >= :today AND c.fechaHoraProgramada < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->groupBy('c.estado')
            ->getQuery()
            ->getResult();

        $pieLabels = [];
        $pieData = [];
        $pieColors = [];
        $colorMap = [
            \App\Enum\CirugiaEstados::PROGRAMADA->name => '#ffc107',
            \App\Enum\CirugiaEstados::EN_SALA->name => '#17a2b8',
            \App\Enum\CirugiaEstados::PRE_OP->name => '#6610f2',
            \App\Enum\CirugiaEstados::TRANS_OP->name => '#007bff',
            \App\Enum\CirugiaEstados::POST_OP->name => '#fd7e14',
            \App\Enum\CirugiaEstados::FINALIZADA->name => '#28a745',
            \App\Enum\CirugiaEstados::CANCELADA->name => '#dc3545',
        ];

        $totalHoy = 0;
        foreach ($cirugiasHoyRaw as $row) {
            /** @var \App\Enum\CirugiaEstados $estado */
            $estado = $row['estado'];
            $pieLabels[] = $estado->getReadableText();
            $pieData[] = $row['total'];
            $pieColors[] = $colorMap[$estado->name] ?? '#cccccc';
            $totalHoy += $row['total'];
        }

        // Chart 2 (Bar/Area): Cirugías por los últimos 7 días
        $sevenDaysAgo = (new \DateTime('-7 days'))->setTime(0, 0, 0);
        $diasLabels = [];
        $semanaData = array_fill(0, 7, 0);

        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-$i days");
            $diasLabels[] = $date->format('d/m');
        }

        $cirugiasSemana = $em->getRepository(Cirugia::class)->createQueryBuilder('c')
            ->select('c.fechaHoraProgramada as fecha')
            ->where('c.fechaHoraProgramada >= :start')
            ->setParameter('start', $sevenDaysAgo)
            ->getQuery()
            ->getResult();

        foreach ($cirugiasSemana as $row) {
            $fecha = $row['fecha'];
            $diff = (new \DateTime('today'))->diff((clone $fecha)->setTime(0,0,0))->days;
            $index = 6 - $diff;
            if ($index >= 0 && $index <= 6) {
                $semanaData[$index]++;
            }
        }

        return $this->json([
            'cards' => [
                'card1' => $agendadas,
                'card2' => $enProceso,
                'card3' => $finalizadasHoy,
                'card4' => $quirofanosTotales,
            ],
            'charts' => [
                'bar' => [
                    'labels' => $diasLabels,
                    'datasets' => [
                        [
                            'label' => 'Cirugías',
                            'backgroundColor' => 'rgba(255, 193, 7, 0.2)',
                            'borderColor' => '#ffc107',
                            'data' => $semanaData,
                            'fill' => true,
                            'tension' => 0.4
                        ]
                    ]
                ],
                'pie' => [
                    'labels' => $pieLabels,
                    'datasets' => [
                        [
                            'data' => $pieData,
                            'backgroundColor' => $pieColors
                        ]
                    ],
                    'totalHoy' => $totalHoy
                ]
            ]
        ]);
    }
    private function emergenciaDashboard(): Response
    {
        return $this->render('dashboard/emergencia.html.twig');
    }

    #[Route('/api/dashboard/emergencia', name: 'api_dashboard_emergencia', methods: ['GET'])]
    public function getEmergenciaDashboardData(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ER');

        // Card 1: Esperando Triaje
        $esperandoTriaje = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.estado = :estado')
            ->setParameter('estado', \App\Enum\EmergenciasEstados::WAITING_TRIAGE)
            ->getQuery()
            ->getSingleScalarResult();

        // Card 2: Esperando Cama
        $esperandoCama = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.estado = :estado')
            ->setParameter('estado', \App\Enum\EmergenciasEstados::WAITING_BED)
            ->getQuery()
            ->getSingleScalarResult();

        // Card 3: En Atención (En Cama)
        $enAtencion = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.estado = :estado')
            ->setParameter('estado', \App\Enum\EmergenciasEstados::IN_TREATMENT)
            ->getQuery()
            ->getSingleScalarResult();

        // Card 4: Altas/Finalizadas (Hoy)
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $altasHoy = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.estado = :estado')
            ->andWhere('e.fechaIngreso >= :today AND e.fechaIngreso < :tomorrow')
            ->setParameter('estado', \App\Enum\EmergenciasEstados::DISCHARGED)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        // Chart 1 (Pie): Distribución de Estados Actuales
        $emergenciasRaw = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('e.estado, COUNT(e.id) as total')
            ->where('e.estado != :alta')
            ->setParameter('alta', \App\Enum\EmergenciasEstados::DISCHARGED)
            ->groupBy('e.estado')
            ->getQuery()
            ->getResult();

        $pieLabels = [];
        $pieData = [];
        $pieColors = [];
        $colorMap = [
            \App\Enum\EmergenciasEstados::WAITING_TRIAGE->name => '#dc3545',
            \App\Enum\EmergenciasEstados::WAITING_BED->name => '#ffc107',
            \App\Enum\EmergenciasEstados::IN_TREATMENT->name => '#17a2b8',
            \App\Enum\EmergenciasEstados::DERIVED_CONSULTATION->name => '#6610f2',
        ];

        $totalActivas = 0;
        foreach ($emergenciasRaw as $row) {
            /** @var \App\Enum\EmergenciasEstados $estado */
            $estado = $row['estado'];
            $pieLabels[] = $estado->getReadableText();
            $pieData[] = $row['total'];
            $pieColors[] = $colorMap[$estado->name] ?? '#cccccc';
            $totalActivas += $row['total'];
        }

        // Chart 2 (Bar/Area): Ingresos por los últimos 7 días
        $sevenDaysAgo = (new \DateTime('-7 days'))->setTime(0, 0, 0);
        $diasLabels = [];
        $semanaData = array_fill(0, 7, 0);

        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-$i days");
            $diasLabels[] = $date->format('d/m');
        }

        $emergenciasSemana = $em->getRepository(Emergencia::class)->createQueryBuilder('e')
            ->select('e.fechaIngreso as fecha')
            ->where('e.fechaIngreso >= :start')
            ->setParameter('start', $sevenDaysAgo)
            ->getQuery()
            ->getResult();

        foreach ($emergenciasSemana as $row) {
            $fecha = $row['fecha'];
            $diff = (new \DateTime('today'))->diff((clone $fecha)->setTime(0,0,0))->days;
            $index = 6 - $diff;
            if ($index >= 0 && $index <= 6) {
                $semanaData[$index]++;
            }
        }

        return $this->json([
            'cards' => [
                'card1' => $esperandoTriaje,
                'card2' => $esperandoCama,
                'card3' => $enAtencion,
                'card4' => $altasHoy,
            ],
            'charts' => [
                'bar' => [
                    'labels' => $diasLabels,
                    'datasets' => [
                        [
                            'label' => 'Ingresos',
                            'backgroundColor' => 'rgba(220, 53, 69, 0.2)', // Danger red
                            'borderColor' => '#dc3545',
                            'data' => $semanaData,
                            'fill' => true,
                            'tension' => 0.4
                        ]
                    ]
                ],
                'pie' => [
                    'labels' => $pieLabels,
                    'datasets' => [
                        [
                            'data' => $pieData,
                            'backgroundColor' => $pieColors
                        ]
                    ],
                    'totalHoy' => $totalActivas
                ]
            ]
        ]);
    }
}

