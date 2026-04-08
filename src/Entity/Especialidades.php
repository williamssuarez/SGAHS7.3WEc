<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\EspecialidadesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EspecialidadesRepository::class)]
class Especialidades
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
     * @var Collection<int, CitasSolicitudes>
     */
    #[ORM\OneToMany(targetEntity: CitasSolicitudes::class, mappedBy: 'especialidad')]
    private Collection $citasSolicitudes;

    /**
     * @var Collection<int, Citas>
     */
    #[ORM\OneToMany(targetEntity: Citas::class, mappedBy: 'especialidad')]
    private Collection $citas;

    /**
     * @var Collection<int, Consulta>
     */
    #[ORM\OneToMany(targetEntity: Consulta::class, mappedBy: 'especialidad')]
    private Collection $consultas;

    public function __construct()
    {
        $this->citasSolicitudes = new ArrayCollection();
        $this->citas = new ArrayCollection();
        $this->consultas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s: %s',
            $this->getNombre(),
            $this->getDescripcion(),
        );
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
     * @return Collection<int, CitasSolicitudes>
     */
    public function getCitasSolicitudes(): Collection
    {
        return $this->citasSolicitudes;
    }

    public function addCitasSolicitude(CitasSolicitudes $citasSolicitude): static
    {
        if (!$this->citasSolicitudes->contains($citasSolicitude)) {
            $this->citasSolicitudes->add($citasSolicitude);
            $citasSolicitude->setEspecialidad($this);
        }

        return $this;
    }

    public function removeCitasSolicitude(CitasSolicitudes $citasSolicitude): static
    {
        if ($this->citasSolicitudes->removeElement($citasSolicitude)) {
            // set the owning side to null (unless already changed)
            if ($citasSolicitude->getEspecialidad() === $this) {
                $citasSolicitude->setEspecialidad(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Citas>
     */
    public function getCitas(): Collection
    {
        return $this->citas;
    }

    public function addCita(Citas $cita): static
    {
        if (!$this->citas->contains($cita)) {
            $this->citas->add($cita);
            $cita->setEspecialidad($this);
        }

        return $this;
    }

    public function removeCita(Citas $cita): static
    {
        if ($this->citas->removeElement($cita)) {
            // set the owning side to null (unless already changed)
            if ($cita->getEspecialidad() === $this) {
                $cita->setEspecialidad(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consulta>
     */
    public function getConsultas(): Collection
    {
        return $this->consultas;
    }

    public function addConsulta(Consulta $consulta): static
    {
        if (!$this->consultas->contains($consulta)) {
            $this->consultas->add($consulta);
            $consulta->setEspecialidad($this);
        }

        return $this;
    }

    public function removeConsulta(Consulta $consulta): static
    {
        if ($this->consultas->removeElement($consulta)) {
            // set the owning side to null (unless already changed)
            if ($consulta->getEspecialidad() === $this) {
                $consulta->setEspecialidad(null);
            }
        }

        return $this;
    }
}
