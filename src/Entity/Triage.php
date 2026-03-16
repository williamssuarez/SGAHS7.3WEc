<?php

namespace App\Entity;

use App\Entity\Traits\CoreVitalesTrait;
use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\TriageNivelesPrioridad;
use App\Repository\TriageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: TriageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Triage
{
    use CoreVitalesTrait;
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Gedmo\Versioned]
    private ?string $motivoIngreso = null;

    #[ORM\Column]
    private ?\DateTime $fechaEvaluacion = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "El valor de la escala no es posible (3 - 15).", min: 3, max: 15)]
    #[Gedmo\Versioned]
    private ?int $escalaGlasgow = null;

    #[ORM\Column(enumType: TriageNivelesPrioridad::class)]
    private ?TriageNivelesPrioridad $nivelPrioridad = null;

    public function __construct()
    {
        $this->fechaEvaluacion = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotivoIngreso(): ?string
    {
        return $this->motivoIngreso;
    }

    public function setMotivoIngreso(string $motivoIngreso): static
    {
        $this->motivoIngreso = $motivoIngreso;

        return $this;
    }

    public function getFechaEvaluacion(): ?\DateTime
    {
        return $this->fechaEvaluacion;
    }

    public function setFechaEvaluacion(\DateTime $fechaEvaluacion): static
    {
        $this->fechaEvaluacion = $fechaEvaluacion;

        return $this;
    }

    public function getEscalaGlasgow(): ?int
    {
        return $this->escalaGlasgow;
    }

    public function setEscalaGlasgow(?int $escalaGlasgow): static
    {
        $this->escalaGlasgow = $escalaGlasgow;

        return $this;
    }

    public function getNivelPrioridad(): ?TriageNivelesPrioridad
    {
        return $this->nivelPrioridad;
    }

    public function setNivelPrioridad(TriageNivelesPrioridad $nivelPrioridad): static
    {
        $this->nivelPrioridad = $nivelPrioridad;

        return $this;
    }

    #[Assert\Callback]
    public function validateIdentity(ExecutionContextInterface $context, $payload): void
    {
        // Rule 1: Cannot have both empty
        if (($this->escalaGlasgow < 3) || $this->escalaGlasgow > 15) {
            $context->buildViolation('La escala debe estar entre el 3 y el 15 .')
                ->atPath('escalaGlasgow')
                ->addViolation();
        }
    }

    public function getTriageNivelesPrioridadBadgeConfig(): array
    {
        switch ($this->getNivelPrioridad()) {
            case TriageNivelesPrioridad::LEVEL_1:
                return [
                    'class' => 'text-bg-danger',
                    'label' => TriageNivelesPrioridad::LEVEL_1->getReadableText()
                ];
            case TriageNivelesPrioridad::LEVEL_2:
                return [
                    'class' => 'text-bg-warning',
                    'label' => TriageNivelesPrioridad::LEVEL_2->getReadableText()
                ];
            case TriageNivelesPrioridad::LEVEL_3:
                return [
                    'class' => 'text-bg-primary',
                    'label' => TriageNivelesPrioridad::LEVEL_3->getReadableText()
                ];
            case TriageNivelesPrioridad::LEVEL_4:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => TriageNivelesPrioridad::LEVEL_4->getReadableText()
                ];
            case TriageNivelesPrioridad::LEVEL_5:
                return [
                    'class' => 'text-bg-dark',
                    'label' => TriageNivelesPrioridad::LEVEL_5->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getStatusColor(string $type): string
    {
        return match($type) {
            'temp' => ($this->temperatura >= 38.0 || $this->temperatura < 36.0) ? 'text-danger' : '',
            'presi' => ($this->paSistolica >= 140 || $this->paDiastolica >= 90) ? 'text-danger' : '',
            'fc'   => ($this->frecuenciaCardiaca > 100 || $this->frecuenciaCardiaca < 60) ? 'text-warning' : '',
            'fr'   => ($this->frecuenciaRespiratoria > 20 || $this->frecuenciaRespiratoria < 12) ? 'text-warning' : '',
            'spo2' => ($this->spo2 < 94) ? 'text-danger' : '',
            default => '',
        };
    }
}
