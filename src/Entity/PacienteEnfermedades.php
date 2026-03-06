<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\PacienteEnfermedadesTipos;
use App\Repository\PacienteEnfermedadesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: PacienteEnfermedadesRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class PacienteEnfermedades
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteEnfermedades')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteEnfermedades')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Versioned]
    private ?Enfermedades $enfermedad = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaDiagnostico = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?bool $cronica = null;

    #[ORM\Column(enumType: PacienteEnfermedadesTipos::class)]
    #[Gedmo\Versioned]
    private ?PacienteEnfermedadesTipos $tipo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $notas = null;

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

    public function getEnfermedad(): ?Enfermedades
    {
        return $this->enfermedad;
    }

    public function setEnfermedad(?Enfermedades $enfermedad): static
    {
        $this->enfermedad = $enfermedad;

        return $this;
    }

    public function getFechaDiagnostico(): ?\DateTime
    {
        if ($this->fechaDiagnostico instanceof \DateTimeInterface) {
            // Force the object to UTC so the Form component doesn't panic
            return $this->fechaDiagnostico->setTimezone(new \DateTimeZone('UTC'));
        }
        return $this->fechaDiagnostico;
    }

    public function setFechaDiagnostico(\DateTime $fechaDiagnostico): static
    {
        $this->fechaDiagnostico = $fechaDiagnostico;

        return $this;
    }

    public function isCronica(): ?bool
    {
        return $this->cronica;
    }

    public function setCronica(bool $cronica): static
    {
        $this->cronica = $cronica;

        return $this;
    }

    public function getTipo(): ?PacienteEnfermedadesTipos
    {
        return $this->tipo;
    }

    public function setTipo(PacienteEnfermedadesTipos $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(?string $notas): static
    {
        $this->notas = $notas;

        return $this;
    }

    public function getPacienteEnfermedadesTiposBadgeConfig(): array
    {
        switch ($this->getTipo()){
            case PacienteEnfermedadesTipos::PRESUMPTIVE:
                return [
                    'class' => 'text-bg-primary',
                    'label' => PacienteEnfermedadesTipos::PRESUMPTIVE->getReadableText()
                ];
            case PacienteEnfermedadesTipos::DEFINITIVE:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => PacienteEnfermedadesTipos::DEFINITIVE->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
