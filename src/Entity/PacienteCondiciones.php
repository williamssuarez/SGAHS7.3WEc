<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\ConsultaTipos;
use App\Enum\PacienteCondicionesEstados;
use App\Repository\PacienteCondicionesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: PacienteCondicionesRepository::class)]
#[Assert\Callback(callback: 'validateDates')]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class PacienteCondiciones
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteCondiciones')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteCondiciones')]
    private ?Condiciones $condicion = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaAparicion = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaFinalizada = null;

    #[ORM\Column(enumType: PacienteCondicionesEstados::class)]
    #[Gedmo\Versioned]
    private ?PacienteCondicionesEstados $estado = null;

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

    public function getCondicion(): ?Condiciones
    {
        return $this->condicion;
    }

    public function setCondicion(?Condiciones $condicion): static
    {
        $this->condicion = $condicion;

        return $this;
    }

    public function getFechaAparicion(): ?\DateTime
    {
        return $this->fechaAparicion;
    }

    public function setFechaAparicion(\DateTime $fechaAparicion): static
    {
        $this->fechaAparicion = $fechaAparicion;

        return $this;
    }

    public function getFechaFinalizada(): ?\DateTime
    {
        return $this->fechaFinalizada;
    }

    public function setFechaFinalizada(?\DateTime $fechaFinalizada): static
    {
        $this->fechaFinalizada = $fechaFinalizada;

        return $this;
    }

    public function getEstado(): ?PacienteCondicionesEstados
    {
        return $this->estado;
    }

    public function setEstado(PacienteCondicionesEstados $estado): static
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

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->fechaFinalizada !== null && $this->fechaAparicion !== null) {
            if ($this->fechaFinalizada < $this->fechaAparicion) {
                $context->buildViolation('La fecha de finalización no puede ser anterior al inicio.')
                    ->atPath('fechaFinalizada')
                    ->addViolation();
            }
        }
    }

    public function getPacienteCondicionesEstadosBadgeConfig(): array
    {
        switch ($this->getEstado()){
            case PacienteCondicionesEstados::ACTIVE:
                return [
                    'class' => 'text-bg-primary',
                    'label' => PacienteCondicionesEstados::ACTIVE->getReadableText()
                ];
            case PacienteCondicionesEstados::CHRONIC:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => PacienteCondicionesEstados::CHRONIC->getReadableText()
                ];
            case PacienteCondicionesEstados::RESOLVED:
                return [
                    'class' => 'text-bg-success',
                    'label' => PacienteCondicionesEstados::RESOLVED->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
