<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ZonaCamaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZonaCamaRepository::class)]
class ZonaCama
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?int $capacidadMaxima = null;

    /**
     * @var Collection<int, Cama>
     */
    #[ORM\OneToMany(targetEntity: Cama::class, mappedBy: 'zona')]
    private Collection $camas;

    public function __construct()
    {
        $this->camas = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (Capacidad Maxima: %s)',
            $this->getNombre(),
            $this->getDescripcion(),
            $this->getCapacidadMaxima(),
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCapacidadMaxima(): ?int
    {
        return $this->capacidadMaxima;
    }

    public function setCapacidadMaxima(int $capacidadMaxima): static
    {
        $this->capacidadMaxima = $capacidadMaxima;

        return $this;
    }

    /**
     * @return Collection<int, Cama>
     */
    public function getCamas(): Collection
    {
        return $this->camas;
    }

    public function addCama(Cama $cama): static
    {
        if (!$this->camas->contains($cama)) {
            $this->camas->add($cama);
            $cama->setZona($this);
        }

        return $this;
    }

    public function removeCama(Cama $cama): static
    {
        if ($this->camas->removeElement($cama)) {
            // set the owning side to null (unless already changed)
            if ($cama->getZona() === $this) {
                $cama->setZona(null);
            }
        }

        return $this;
    }
}
