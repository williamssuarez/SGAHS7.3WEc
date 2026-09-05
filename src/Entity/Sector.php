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

    /**
     * @var Collection<int, InternalProfile>
     */
    #[ORM\OneToMany(targetEntity: InternalProfile::class, mappedBy: 'sector')]
    private Collection $internalProfiles;

    /**
     * @var Collection<int, ExternalProfile>
     */
    #[ORM\OneToMany(targetEntity: ExternalProfile::class, mappedBy: 'sector')]
    private Collection $externalProfiles;

    public function __construct()
    {
        $this->pacientes = new ArrayCollection();
        $this->internalProfiles = new ArrayCollection();
        $this->externalProfiles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nombre;
    }

    public function displayFullLocation()
    {
        return sprintf('Edo. %s, municipio %s, parroquia %s, sector %s, ',
            $this->getParroquia()->getMunicipio()->getEstado()->getNombre(),
            $this->getParroquia()->getMunicipio()->getNombre(),
            $this->getParroquia()->getNombre(),
            $this->getNombre()
        );
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

    /**
     * @return Collection<int, InternalProfile>
     */
    public function getInternalProfiles(): Collection
    {
        return $this->internalProfiles;
    }

    public function addInternalProfile(InternalProfile $internalProfile): static
    {
        if (!$this->internalProfiles->contains($internalProfile)) {
            $this->internalProfiles->add($internalProfile);
            $internalProfile->setSector($this);
        }

        return $this;
    }

    public function removeInternalProfile(InternalProfile $internalProfile): static
    {
        if ($this->internalProfiles->removeElement($internalProfile)) {
            // set the owning side to null (unless already changed)
            if ($internalProfile->getSector() === $this) {
                $internalProfile->setSector(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExternalProfile>
     */
    public function getExternalProfiles(): Collection
    {
        return $this->externalProfiles;
    }

    public function addExternalProfile(ExternalProfile $externalProfile): static
    {
        if (!$this->externalProfiles->contains($externalProfile)) {
            $this->externalProfiles->add($externalProfile);
            $externalProfile->setSector($this);
        }

        return $this;
    }

    public function removeExternalProfile(ExternalProfile $externalProfile): static
    {
        if ($this->externalProfiles->removeElement($externalProfile)) {
            // set the owning side to null (unless already changed)
            if ($externalProfile->getSector() === $this) {
                $externalProfile->setSector(null);
            }
        }

        return $this;
    }
}
