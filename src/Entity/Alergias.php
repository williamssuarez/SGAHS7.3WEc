<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\AlergiasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: AlergiasRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Alergias
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'alergias')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'alergias')]
    #[Gedmo\Versioned]
    private ?Alergenos $alergeno = null;

    /**
     * @var Collection<int, Reacciones>
     */
    #[ORM\ManyToMany(targetEntity: Reacciones::class, inversedBy: 'alergias')]
    private Collection $reacciones;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $severidad = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $observaciones = null;

    public function __construct()
    {
        $this->reacciones = new ArrayCollection();
    }

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

    public function getAlergeno(): ?Alergenos
    {
        return $this->alergeno;
    }

    public function setAlergeno(?Alergenos $alergeno): static
    {
        $this->alergeno = $alergeno;

        return $this;
    }

    /**
     * @return Collection<int, Reacciones>
     */
    public function getReacciones(): Collection
    {
        return $this->reacciones;
    }

    public function addReaccione(Reacciones $reaccione): static
    {
        if (!$this->reacciones->contains($reaccione)) {
            $this->reacciones->add($reaccione);
        }

        return $this;
    }

    public function removeReaccione(Reacciones $reaccione): static
    {
        $this->reacciones->removeElement($reaccione);

        return $this;
    }

    public function getSeveridad(): ?string
    {
        return $this->severidad;
    }

    public function setSeveridad(string $severidad): static
    {
        $this->severidad = $severidad;

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

    public function getAlergiaSeveridadBadgeConfig(): array
    {
        switch ($this->getSeveridad()){
            case 'min':
                return [
                    'class' => 'text-bg-secondary',
                    'label' => 'Minima'
                ];
            case 'mod':
                return [
                    'class' => 'text-bg-warning',
                    'label' => 'Moderada'
                ];
            case 'sev':
                return [
                    'class' => 'text-bg-danger',
                    'label' => 'Severa'
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
