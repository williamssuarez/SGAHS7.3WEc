<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\PacienteDiscapacidadesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: PacienteDiscapacidadesRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class PacienteDiscapacidades
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteDiscapacidades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteDiscapacidade')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Versioned]
    private ?Discapacidades $discapacidad = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Discapacidad fuera de rango (1 - 100)", min: 1, max: 100)]
    #[Gedmo\Versioned]
    private ?int $porcentaje = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?bool $congenita = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $ayudaTecnica = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $limitacionesFuncionales = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $numeroCertificado = null;

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

    public function getDiscapacidad(): ?Discapacidades
    {
        return $this->discapacidad;
    }

    public function setDiscapacidad(?Discapacidades $discapacidad): static
    {
        $this->discapacidad = $discapacidad;

        return $this;
    }

    public function getPorcentaje(): ?int
    {
        return $this->porcentaje;
    }

    public function setPorcentaje(?int $porcentaje): static
    {
        $this->porcentaje = $porcentaje;

        return $this;
    }

    public function isCongenita(): ?bool
    {
        return $this->congenita;
    }

    public function setCongenita(bool $congenita): static
    {
        $this->congenita = $congenita;

        return $this;
    }

    public function getAyudaTecnica(): ?string
    {
        return $this->ayudaTecnica;
    }

    public function setAyudaTecnica(?string $ayudaTecnica): static
    {
        $this->ayudaTecnica = $ayudaTecnica;

        return $this;
    }

    public function getLimitacionesFuncionales(): ?string
    {
        return $this->limitacionesFuncionales;
    }

    public function setLimitacionesFuncionales(?string $limitacionesFuncionales): static
    {
        $this->limitacionesFuncionales = $limitacionesFuncionales;

        return $this;
    }

    public function getNumeroCertificado(): ?string
    {
        return $this->numeroCertificado;
    }

    public function setNumeroCertificado(?string $numeroCertificado): static
    {
        $this->numeroCertificado = $numeroCertificado;

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
