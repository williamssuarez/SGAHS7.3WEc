<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\SectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectorRepository::class)]
class Sector
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'sectores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Parroquia $parroquia = null;

    /**
     * @var Collection<int, Paciente>
     */
    #[ORM\OneToMany(targetEntity: Paciente::class, mappedBy: 'sector')]
    private Collection $pacientes;

    public function __construct()
    {
        $this->pacientes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nombre;
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

    public function getParroquia(): ?Parroquia
    {
        return $this->parroquia;
    }

    public function setParroquia(?Parroquia $parroquia): static
    {
        $this->parroquia = $parroquia;

        return $this;
    }

    /**
     * @return Collection<int, Paciente>
     */
    public function getPacientes(): Collection
    {
        return $this->pacientes;
    }

    public function addPaciente(Paciente $paciente): static
    {
        if (!$this->pacientes->contains($paciente)) {
            $this->pacientes->add($paciente);
            $paciente->setSector($this);
        }

        return $this;
    }

    public function removePaciente(Paciente $paciente): static
    {
        if ($this->pacientes->removeElement($paciente)) {
            // set the owning side to null (unless already changed)
            if ($paciente->getSector() === $this) {
                $paciente->setSector(null);
            }
        }

        return $this;
    }
}
