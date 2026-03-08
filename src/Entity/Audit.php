<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\AuditTipos;
use App\Repository\AuditRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditRepository::class)]
class Audit
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'audits')]
    private ?Paciente $paciente = null;

    #[ORM\Column(enumType: AuditTipos::class)]
    private ?AuditTipos $tipoAudit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $direccionIp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }

    public function getTipoAudit(): ?AuditTipos
    {
        return $this->tipoAudit;
    }

    public function setTipoAudit(AuditTipos $tipoAudit): static
    {
        $this->tipoAudit = $tipoAudit;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getDireccionIp(): ?string
    {
        return $this->direccionIp;
    }

    public function setDireccionIp(?string $direccionIp): static
    {
        $this->direccionIp = $direccionIp;

        return $this;
    }
}
