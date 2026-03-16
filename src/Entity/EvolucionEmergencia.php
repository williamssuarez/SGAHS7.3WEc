<?php

namespace App\Entity;

use App\Entity\Traits\CoreVitalesTrait;
use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\EvolucionEmergenciaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: EvolucionEmergenciaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class EvolucionEmergencia
{
    use CoreVitalesTrait;
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $fechaHora = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $notaClinica = null;

    #[ORM\ManyToOne(inversedBy: 'evolucionEmergencias')]
    private ?Emergencia $emergencia = null;

    public function __construct()
    {
        $this->fechaHora = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaHora(): ?\DateTime
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTime $fechaHora): static
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getNotaClinica(): ?string
    {
        return $this->notaClinica;
    }

    public function setNotaClinica(string $notaClinica): static
    {
        $this->notaClinica = $notaClinica;

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
}
