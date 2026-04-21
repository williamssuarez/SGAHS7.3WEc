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

    #[ORM\ManyToOne(inversedBy: 'audits')]
    private ?Consulta $consulta = null;

    #[ORM\ManyToOne(inversedBy: 'audits')]
    private ?Cirugia $cirugia = null;

    #[ORM\ManyToOne(inversedBy: 'audits')]
    private ?Emergencia $emergencia = null;

    #[ORM\ManyToOne(inversedBy: 'audits')]
    private ?Hospitalizaciones $hospitalizacion = null;

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

    public function getConsulta(): ?Consulta
    {
        return $this->consulta;
    }

    public function setConsulta(?Consulta $consulta): static
    {
        $this->consulta = $consulta;

        return $this;
    }

    public function getCirugia(): ?Cirugia
    {
        return $this->cirugia;
    }

    public function setCirugia(?Cirugia $cirugia): static
    {
        $this->cirugia = $cirugia;

        return $this;
    }

    public function getEmergencia(): ?Emergencia
    {
        return $this->emergencia;
    }

    public function setEmergencia(?Emergencia $emergencia): static
    {
        $this->emergencia = $emergencia;

        return $this;
    }

    public function getHospitalizacion(): ?Hospitalizaciones
    {
        return $this->hospitalizacion;
    }

    public function setHospitalizacion(?Hospitalizaciones $hospitalizacion): static
    {
        $this->hospitalizacion = $hospitalizacion;

        return $this;
    }
}
