<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\EmergenciasCondicionAlta;
use App\Repository\AltaMedicaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AltaMedicaRepository::class)]
class AltaMedica
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'altaMedica', cascade: ['persist', 'remove'])]
    private ?Emergencia $emergencia = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnosticoFinal = null;

    #[ORM\Column(enumType: EmergenciasCondicionAlta::class)]
    private ?EmergenciasCondicionAlta $condicionAlta = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $indicacionesMedicas = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaEgreso = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hospitalDestino = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motivoTraslado = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $servicioIngreso = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $fechaMuerte = null;

    #[ORM\ManyToOne(inversedBy: 'altaMedicas')]
    private ?Area $areaHospitalizacion = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDiagnosticoFinal(): ?string
    {
        return $this->diagnosticoFinal;
    }

    public function setDiagnosticoFinal(string $diagnosticoFinal): static
    {
        $this->diagnosticoFinal = $diagnosticoFinal;

        return $this;
    }

    public function getCondicionAlta(): ?EmergenciasCondicionAlta
    {
        return $this->condicionAlta;
    }

    public function setCondicionAlta(EmergenciasCondicionAlta $condicionAlta): static
    {
        $this->condicionAlta = $condicionAlta;

        return $this;
    }

    public function getIndicacionesMedicas(): ?string
    {
        return $this->indicacionesMedicas;
    }

    public function setIndicacionesMedicas(?string $indicacionesMedicas): static
    {
        $this->indicacionesMedicas = $indicacionesMedicas;

        return $this;
    }

    public function getFechaEgreso(): ?\DateTimeImmutable
    {
        return $this->fechaEgreso;
    }

    public function setFechaEgreso(\DateTimeImmutable $fechaEgreso): static
    {
        $this->fechaEgreso = $fechaEgreso;

        return $this;
    }

    public function getHospitalDestino(): ?string
    {
        return $this->hospitalDestino;
    }

    public function setHospitalDestino(?string $hospitalDestino): static
    {
        $this->hospitalDestino = $hospitalDestino;

        return $this;
    }

    public function getMotivoTraslado(): ?string
    {
        return $this->motivoTraslado;
    }

    public function setMotivoTraslado(?string $motivoTraslado): static
    {
        $this->motivoTraslado = $motivoTraslado;

        return $this;
    }

    public function getServicioIngreso(): ?string
    {
        return $this->servicioIngreso;
    }

    public function setServicioIngreso(?string $servicioIngreso): static
    {
        $this->servicioIngreso = $servicioIngreso;

        return $this;
    }

    public function getFechaMuerte(): ?\DateTimeImmutable
    {
        return $this->fechaMuerte;
    }

    public function setFechaMuerte(?\DateTimeImmutable $fechaMuerte): static
    {
        $this->fechaMuerte = $fechaMuerte;

        return $this;
    }

    public function getCondicionAltaBadgeConfig(): array
    {
        switch ($this->getCondicionAlta()) {
            case EmergenciasCondicionAlta::SENT_HOME:
                return [
                    'class' => 'text-bg-success',
                    'label' => EmergenciasCondicionAlta::SENT_HOME->getReadableText()
                ];
            case EmergenciasCondicionAlta::ADMITTED_ROOM:
                return [
                    'class' => 'text-bg-primary',
                    'label' => EmergenciasCondicionAlta::ADMITTED_ROOM->getReadableText()
                ];
            case EmergenciasCondicionAlta::TRANSFER:
                return [
                    'class' => 'text-bg-warning',
                    'label' => EmergenciasCondicionAlta::TRANSFER->getReadableText()
                ];
            case EmergenciasCondicionAlta::LEFT:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => EmergenciasCondicionAlta::LEFT->getReadableText()
                ];
            case EmergenciasCondicionAlta::DECEASED:
                return [
                    'class' => 'text-bg-dark',
                    'label' => EmergenciasCondicionAlta::DECEASED->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getAreaHospitalizacion(): ?Area
    {
        return $this->areaHospitalizacion;
    }

    public function setAreaHospitalizacion(?Area $areaHospitalizacion): static
    {
        $this->areaHospitalizacion = $areaHospitalizacion;

        return $this;
    }
}
