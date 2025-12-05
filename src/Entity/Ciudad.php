<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\CiudadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CiudadRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Ciudad
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $poblacion = null;

    /**
     * @var Collection<int, Empleado>
     */
    #[ORM\OneToMany(targetEntity: Empleado::class, mappedBy: 'ciudad')]
    private Collection $empleados;

    public function __construct()
    {
        $this->empleados = new ArrayCollection();
    }

    public function __toString(){
        return $this->getNombre();
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

    public function getPoblacion(): ?int
    {
        return $this->poblacion;
    }

    public function setPoblacion(int $poblacion): static
    {
        $this->poblacion = $poblacion;

        return $this;
    }

    /**
     * @return Collection<int, Empleado>
     */
    public function getEmpleados(): Collection
    {
        return $this->empleados;
    }

    public function addEmpleado(Empleado $empleado): static
    {
        if (!$this->empleados->contains($empleado)) {
            $this->empleados->add($empleado);
            $empleado->setCiudad($this);
        }

        return $this;
    }

    public function removeEmpleado(Empleado $empleado): static
    {
        if ($this->empleados->removeElement($empleado)) {
            // set the owning side to null (unless already changed)
            if ($empleado->getCiudad() === $this) {
                $empleado->setCiudad(null);
            }
        }

        return $this;
    }
}
