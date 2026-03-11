<?php

namespace App\Service;

use App\Entity\Audit;
use App\Entity\Consulta;
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

    public function persistAudit(AuditTipos $tipo, string $mensaje, ?Paciente $paciente = null, ?Consulta $consulta = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new Audit();
        $log->setTipoAudit($tipo);
        $log->setDescripcion($mensaje);
        $log->setPaciente($paciente);
        $log->setConsulta($consulta);
        $log->setDireccionIp($request?->getClientIp());

        $this->em->persist($log);
    }

    public function persistAndFlushAudit(AuditTipos $tipo, string $mensaje, ?Paciente $paciente = null, ?Consulta $consulta = null): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new Audit();
        $log->setTipoAudit($tipo);
        $log->setDescripcion($mensaje);
        $log->setPaciente($paciente);
        $log->setConsulta($consulta);
        $log->setDireccionIp($request?->getClientIp());

        $this->em->persist($log);
        $this->em->flush();
    }

    public function persistEditionAndFlushAudit(
        object $entity,
        AuditTipos $tipo,
        ?Paciente $paciente = null,
        ?Consulta $consulta = null
    ): void {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        if (empty($changeset)) {
            return;
        }

        $details = [];
        foreach ($changeset as $field => $values) {
            // Ignorar campos técnicos si lo deseas (ej: updatedAt)
            if ($field === 'updatedAt') continue;

            $old = $this->formatValue($values[0]);
            $new = $this->formatValue($values[1]);

            $details[] = "$field: de '$old' a '$new'";
        }

        $mensaje = "Edición de " . (new \ReflectionClass($entity))->getShortName() . ": " . implode(', ', $details);

        $this->persistAndFlushAudit($tipo, $mensaje, $paciente, $consulta);
    }

    public function persistEditionAudit(
        object $entity,
        AuditTipos $tipo,
        ?Paciente $paciente = null,
        ?Consulta $consulta = null
    ): void {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        if (empty($changeset)) {
            return;
        }

        $details = [];
        foreach ($changeset as $field => $values) {
            // Ignorar campos técnicos si lo deseas (ej: updatedAt)
            if ($field === 'updatedAt') continue;

            $old = $this->formatValue($values[0]);
            $new = $this->formatValue($values[1]);

            $details[] = "$field: de '$old' a '$new'";
        }

        $mensaje = "Edición de " . (new \ReflectionClass($entity))->getShortName() . ": " . implode(', ', $details);

        $this->persistAudit($tipo, $mensaje, $paciente, $consulta);
    }

    private function formatValue($value): string
    {
        if ($value === null) return 'N/A';

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if ($value instanceof \UnitEnum) {
            // Intenta obtener el label legible si tienes el método, si no, usa el value/name
            return method_exists($value, 'getReadableText') ? $value->getReadableText() : $value->value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return (string) $value;
    }
}
