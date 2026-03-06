<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\DiscapacidadesTipos;
use App\Repository\DiscapacidadesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: DiscapacidadesRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Discapacidades
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $descripcion = null;

    #[ORM\Column(nullable: true, enumType: DiscapacidadesTipos::class)]
    #[Gedmo\Versioned]
    private ?DiscapacidadesTipos $tipo = null;

    /**
     * @var Collection<int, PacienteDiscapacidades>
     */
    #[ORM\OneToMany(targetEntity: PacienteDiscapacidades::class, mappedBy: 'discapacidad')]
    private Collection $pacienteDiscapacidade;

    public function __construct()
    {
        $this->pacienteDiscapacidade = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getTipo(): ?DiscapacidadesTipos
    {
        return $this->tipo;
    }

    public function setTipo(?DiscapacidadesTipos $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getDiscapacidadesTiposBadgeConfig(): array
    {
        switch ($this->getTipo()) {
            case DiscapacidadesTipos::D_PHYSICAL:
                return [
                    'class' => 'text-bg-warning',
                    'label' => DiscapacidadesTipos::D_PHYSICAL->getReadableText()
                ];
            case DiscapacidadesTipos::D_SENSORY:
                return [
                    'class' => 'text-bg-primary',
                    'label' => DiscapacidadesTipos::D_SENSORY->getReadableText()
                ];
            case DiscapacidadesTipos::D_INTELLECTUAL:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => DiscapacidadesTipos::D_INTELLECTUAL->getReadableText()
                ];
            case DiscapacidadesTipos::D_ORGANIC:
                return [
                    'class' => 'text-bg-danger',
                    'label' => DiscapacidadesTipos::D_ORGANIC->getReadableText()
                ];
            case DiscapacidadesTipos::D_PSYCHOSOCIAL:
                return [
                    'class' => 'text-bg-dark',
                    'label' => DiscapacidadesTipos::D_PSYCHOSOCIAL->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    /**
     * @return Collection<int, PacienteDiscapacidades>
     */
    public function getPacienteDiscapacidade(): Collection
    {
        return $this->pacienteDiscapacidade;
    }

    public function addPacienteDiscapacidade(PacienteDiscapacidades $pacienteDiscapacidade): static
    {
        if (!$this->pacienteDiscapacidade->contains($pacienteDiscapacidade)) {
            $this->pacienteDiscapacidade->add($pacienteDiscapacidade);
            $pacienteDiscapacidade->setDiscapacidad($this);
        }

        return $this;
    }

    public function removePacienteDiscapacidade(PacienteDiscapacidades $pacienteDiscapacidade): static
    {
        if ($this->pacienteDiscapacidade->removeElement($pacienteDiscapacidade)) {
            // set the owning side to null (unless already changed)
            if ($pacienteDiscapacidade->getDiscapacidad() === $this) {
                $pacienteDiscapacidade->setDiscapacidad(null);
            }
        }

        return $this;
    }
}
