<?php

namespace App\Entity;

use App\Entity\Traits\CoreVitalesTrait;
use App\Repository\TriageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: TriageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Triage
{
    use CoreVitalesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?int $nivelPrioridad = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Gedmo\Versioned]
    private ?string $motivoIngreso = null;

    #[ORM\Column]
    private ?\DateTime $fechaEvaluacion = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Versioned]
    private ?int $escalaGlasgow = null;

    public function __construct()
    {
        $this->fechaEvaluacion = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNivelPrioridad(): ?int
    {
        return $this->nivelPrioridad;
    }

    public function setNivelPrioridad(int $nivelPrioridad): static
    {
        $this->nivelPrioridad = $nivelPrioridad;

        return $this;
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
}
