<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\HabitacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HabitacionRepository::class)]
class Habitacion
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $ubicacion = null;

    #[ORM\ManyToOne(inversedBy: 'habitaciones')]
    private ?Area $area = null;

    /**
     * @var Collection<int, HospitalizacionCama>
     */
    #[ORM\OneToMany(targetEntity: HospitalizacionCama::class, mappedBy: 'habitacion')]
    private Collection $camasHospitalizaciones;

    public function __construct()
    {
        $this->camasHospitalizaciones = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->nombre, $this->ubicacion);
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

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(string $ubicacion): static
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection<int, HospitalizacionCama>
     */
    public function getCamasHospitalizaciones(): Collection
    {
        return $this->camasHospitalizaciones;
    }

    public function addCamasHospitalizacione(HospitalizacionCama $camasHospitalizacione): static
    {
        if (!$this->camasHospitalizaciones->contains($camasHospitalizacione)) {
            $this->camasHospitalizaciones->add($camasHospitalizacione);
            $camasHospitalizacione->setHabitacion($this);
        }

        return $this;
    }

    public function removeCamasHospitalizacione(HospitalizacionCama $camasHospitalizacione): static
    {
        if ($this->camasHospitalizaciones->removeElement($camasHospitalizacione)) {
            // set the owning side to null (unless already changed)
            if ($camasHospitalizacione->getHabitacion() === $this) {
                $camasHospitalizacione->setHabitacion(null);
            }
        }

        return $this;
    }
}
