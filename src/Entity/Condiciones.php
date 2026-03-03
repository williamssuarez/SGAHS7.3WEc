<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\CondicionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CondicionesRepository::class)]
class Condiciones
{

    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    /**
     * @var Collection<int, PacienteCondiciones>
     */
    #[ORM\OneToMany(targetEntity: PacienteCondiciones::class, mappedBy: 'condicion')]
    private Collection $pacienteCondiciones;

    public function __construct()
    {
        $this->pacienteCondiciones = new ArrayCollection();
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

    /**
     * @return Collection<int, PacienteCondiciones>
     */
    public function getPacienteCondiciones(): Collection
    {
        return $this->pacienteCondiciones;
    }

    public function addPacienteCondicione(PacienteCondiciones $pacienteCondicione): static
    {
        if (!$this->pacienteCondiciones->contains($pacienteCondicione)) {
            $this->pacienteCondiciones->add($pacienteCondicione);
            $pacienteCondicione->setCondicion($this);
        }

        return $this;
    }

    public function removePacienteCondicione(PacienteCondiciones $pacienteCondicione): static
    {
        if ($this->pacienteCondiciones->removeElement($pacienteCondicione)) {
            // set the owning side to null (unless already changed)
            if ($pacienteCondicione->getCondicion() === $this) {
                $pacienteCondicione->setCondicion(null);
            }
        }

        return $this;
    }
}
