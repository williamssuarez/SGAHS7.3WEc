<?php

namespace App\Service;

use App\Entity\Audit;
use App\Entity\Paciente;
use App\Enum\AuditTipos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class AuditService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack           $requestStack
    ) {}

    public function persistAudit(AuditTipos $tipo, string $mensaje, ?Paciente $paciente = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new Audit();
        $log->setTipoAudit($tipo);
        $log->setDescripcion($mensaje);
        $log->setPaciente($paciente);
        $log->setDireccionIp($request?->getClientIp());

        $this->em->persist($log);
    }

    public function persistAndFlushAudit(AuditTipos $tipo, string $mensaje, ?Paciente $paciente = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new Audit();
        $log->setTipoAudit($tipo);
        $log->setDescripcion($mensaje);
        $log->setPaciente($paciente);
        $log->setDireccionIp($request?->getClientIp());

        $this->em->persist($log);
        $this->em->flush();
    }
}
