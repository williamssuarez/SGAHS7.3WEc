<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\PrescripcionesEstados;
use App\Repository\PrescripcionesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: PrescripcionesRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Prescripciones
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prescripciones')]
    #[Gedmo\Versioned]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'prescripciones')]
    #[Gedmo\Versioned]
    private ?Medicamentos $medicamento = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $dosis = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $frecuencia = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Gedmo\Versioned]
    private ?string $detallesFrecuencia = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaInicio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaFin = null;

    #[ORM\Column(enumType: PrescripcionesEstados::class)]
    #[Gedmo\Versioned]
    private ?PrescripcionesEstados $estado = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $observaciones = null;

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

    public function getMedicamento(): ?Medicamentos
    {
        return $this->medicamento;
    }

    public function setMedicamento(?Medicamentos $medicamento): static
    {
        $this->medicamento = $medicamento;

        return $this;
    }

    public function getDosis(): ?string
    {
        return $this->dosis;
    }

    public function setDosis(string $dosis): static
    {
        $this->dosis = $dosis;

        return $this;
    }

    public function getFrecuencia(): ?string
    {
        return $this->frecuencia;
    }

    public function setFrecuencia(string $frecuencia): static
    {
        $this->frecuencia = $frecuencia;

        return $this;
    }

    public function getDetallesFrecuencia(): ?string
    {
        return $this->detallesFrecuencia;
    }

    public function setDetallesFrecuencia(string $detallesFrecuencia): static
    {
        $this->detallesFrecuencia = $detallesFrecuencia;

        return $this;
    }

    public function getFechaInicio(): ?\DateTime
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTime $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTime
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTime $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getEstado(): ?PrescripcionesEstados
    {
        return $this->estado;
    }

    public function setEstado(PrescripcionesEstados $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }
}
