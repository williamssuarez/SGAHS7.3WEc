<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    private string $flagPath;

    public function __construct(
        ParameterBagInterface $params,
        private readonly Environment $twig
    ) {
        $this->flagPath = $params->get('kernel.project_dir') . '/var/maintenance.flag';
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Solo actuar sobre la petición principal
        if (!$event->isMainRequest()) {
            return;
        }

        // Si el archivo de bandera existe, estamos en mantenimiento
        if (file_exists($this->flagPath)) {
            // Renderizar la vista de mantenimiento
            $content = $this->twig->render('maintenance.html.twig');

            $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);

            // Reemplazar la respuesta, evitando que continúe hacia el controlador
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        // Se ejecuta muy temprano en el ciclo de vida de la petición (alta prioridad)
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }
}
