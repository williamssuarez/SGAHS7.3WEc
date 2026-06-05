<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ParroquiaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParroquiaRepository::class)]
class Parroquia
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'parroquias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Municipio $municipio = null;

    /**
     * @var Collection<int, Sector>
     */
    #[ORM\OneToMany(targetEntity: Sector::class, mappedBy: 'parroquia')]
    private Collection $sectores;

    public function __construct()
    {
        $this->sectores = new ArrayCollection();
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

    public function getMunicipio(): ?Municipio
    {
        return $this->municipio;
    }

    public function setMunicipio(?Municipio $municipio): static
    {
        $this->municipio = $municipio;

        return $this;
    }

    /**
     * @return Collection<int, Sector>
     */
    public function getSectores(): Collection
    {
        return $this->sectores;
    }

    public function addSectore(Sector $sectore): static
    {
        if (!$this->sectores->contains($sectore)) {
            $this->sectores->add($sectore);
            $sectore->setParroquia($this);
        }

        return $this;
    }

    public function removeSectore(Sector $sectore): static
    {
        if ($this->sectores->removeElement($sectore)) {
            // set the owning side to null (unless already changed)
            if ($sectore->getParroquia() === $this) {
                $sectore->setParroquia(null);
            }
        }

        return $this;
    }
}
