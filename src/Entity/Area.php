<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    /**
     * @var Collection<int, Habitacion>
     */
    #[ORM\OneToMany(targetEntity: Habitacion::class, mappedBy: 'area')]
    private Collection $habitaciones;

    /**
     * @var Collection<int, HorarioVisitas>
     */
    #[ORM\OneToMany(targetEntity: HorarioVisitas::class, mappedBy: 'area')]
    private Collection $horarioVisitas;

    /**
     * @var Collection<int, AltaMedica>
     */
    #[ORM\OneToMany(targetEntity: AltaMedica::class, mappedBy: 'areaHospitalizacion')]
    private Collection $altaMedicas;

    public function __construct()
    {
        $this->habitaciones = new ArrayCollection();
        $this->horarioVisitas = new ArrayCollection();
        $this->altaMedicas = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s: %s',
            $this->getNombre(),
            $this->getDescripcion(),
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

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * @return Collection<int, Habitacion>
     */
    public function getHabitaciones(): Collection
    {
        return $this->habitaciones;
    }

    public function addHabitacione(Habitacion $habitacione): static
    {
        if (!$this->habitaciones->contains($habitacione)) {
            $this->habitaciones->add($habitacione);
            $habitacione->setArea($this);
        }

        return $this;
    }

    public function removeHabitacione(Habitacion $habitacione): static
    {
        if ($this->habitaciones->removeElement($habitacione)) {
            // set the owning side to null (unless already changed)
            if ($habitacione->getArea() === $this) {
                $habitacione->setArea(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HorarioVisitas>
     */
    public function getHorarioVisitas(): Collection
    {
        return $this->horarioVisitas;
    }

    public function addHorarioVisita(HorarioVisitas $horarioVisita): static
    {
        if (!$this->horarioVisitas->contains($horarioVisita)) {
            $this->horarioVisitas->add($horarioVisita);
            $horarioVisita->setArea($this);
        }

        return $this;
    }

    public function removeHorarioVisita(HorarioVisitas $horarioVisita): static
    {
        if ($this->horarioVisitas->removeElement($horarioVisita)) {
            // set the owning side to null (unless already changed)
            if ($horarioVisita->getArea() === $this) {
                $horarioVisita->setArea(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AltaMedica>
     */
    public function getAltaMedicas(): Collection
    {
        return $this->altaMedicas;
    }

    public function addAltaMedica(AltaMedica $altaMedica): static
    {
        if (!$this->altaMedicas->contains($altaMedica)) {
            $this->altaMedicas->add($altaMedica);
            $altaMedica->setAreaHospitalizacion($this);
        }

        return $this;
    }

    public function removeAltaMedica(AltaMedica $altaMedica): static
    {
        if ($this->altaMedicas->removeElement($altaMedica)) {
            // set the owning side to null (unless already changed)
            if ($altaMedica->getAreaHospitalizacion() === $this) {
                $altaMedica->setAreaHospitalizacion(null);
            }
        }

        return $this;
    }
}
