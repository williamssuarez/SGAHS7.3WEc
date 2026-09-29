<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ConsultoriosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultoriosRepository::class)]
class Consultorios
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $observaciones = null;

    /**
     * @var Collection<int, CitasConfiguraciones>
     */
    #[ORM\ManyToMany(targetEntity: CitasConfiguraciones::class, mappedBy: 'consultorio')]
    private Collection $citasConfiguraciones;

    /**
     * @var Collection<int, Citas>
     */
    #[ORM\OneToMany(targetEntity: Citas::class, mappedBy: 'consultorio')]
    private Collection $citas;

    public function __construct()
    {
        $this->citasConfiguraciones = new ArrayCollection();
        $this->citas = new ArrayCollection();
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

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * @return Collection<int, CitasConfiguraciones>
     */
    public function getCitasConfiguraciones(): Collection
    {
        return $this->citasConfiguraciones;
    }

    public function addCitasConfiguracione(CitasConfiguraciones $citasConfiguracione): static
    {
        if (!$this->citasConfiguraciones->contains($citasConfiguracione)) {
            $this->citasConfiguraciones->add($citasConfiguracione);
            $citasConfiguracione->addConsultorio($this);
        }

        return $this;
    }

    public function removeCitasConfiguracione(CitasConfiguraciones $citasConfiguracione): static
    {
        if ($this->citasConfiguraciones->removeElement($citasConfiguracione)) {
            $citasConfiguracione->removeConsultorio($this);
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
            $cita->setConsultorio($this);
        }

        return $this;
    }

    public function removeCita(Citas $cita): static
    {
        if ($this->citas->removeElement($cita)) {
            // set the owning side to null (unless already changed)
            if ($cita->getConsultorio() === $this) {
                $cita->setConsultorio(null);
            }
        }

        return $this;
    }
}
