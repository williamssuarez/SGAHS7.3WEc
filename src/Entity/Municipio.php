<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\MunicipioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MunicipioRepository::class)]
class Municipio
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\ManyToOne(inversedBy: 'municipios')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Estado $estado = null;

    /**
     * @var Collection<int, Parroquia>
     */
    #[ORM\OneToMany(targetEntity: Parroquia::class, mappedBy: 'municipio')]
    private Collection $parroquias;

    public function __toString(): string
    {
        return $this->nombre;
    }

    public function __construct()
    {
        $this->parroquias = new ArrayCollection();
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

    public function getEstado(): ?Estado
    {
        return $this->estado;
    }

    public function setEstado(?Estado $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, Parroquia>
     */
    public function getParroquias(): Collection
    {
        return $this->parroquias;
    }

    public function addParroquia(Parroquia $parroquia): static
    {
        if (!$this->parroquias->contains($parroquia)) {
            $this->parroquias->add($parroquia);
            $parroquia->setMunicipio($this);
        }

        return $this;
    }

    public function removeParroquia(Parroquia $parroquia): static
    {
        if ($this->parroquias->removeElement($parroquia)) {
            // set the owning side to null (unless already changed)
            if ($parroquia->getMunicipio() === $this) {
                $parroquia->setMunicipio(null);
            }
        }

        return $this;
    }
}
