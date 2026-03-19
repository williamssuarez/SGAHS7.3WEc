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

    #[ORM\Column(type: Types::TEXT)]
    private ?string $indicacionesMedicas = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fechaEgreso = null;

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

    public function setIndicacionesMedicas(string $indicacionesMedicas): static
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
}
