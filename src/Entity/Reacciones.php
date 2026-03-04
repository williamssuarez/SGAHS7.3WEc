<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ReaccionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReaccionesRepository::class)]
class Reacciones
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

    /**
     * @var Collection<int, Alergias>
     */
    #[ORM\ManyToMany(targetEntity: Alergias::class, mappedBy: 'reacciones')]
    private Collection $alergias;

    /**
     * @var Collection<int, PacienteInmunizaciones>
     */
    #[ORM\ManyToMany(targetEntity: PacienteInmunizaciones::class, mappedBy: 'reacciones')]
    private Collection $pacienteInmunizaciones;

    public function __construct()
    {
        $this->alergias = new ArrayCollection();
        $this->pacienteInmunizaciones = new ArrayCollection();
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

    /**
     * @return Collection<int, Alergias>
     */
    public function getAlergias(): Collection
    {
        return $this->alergias;
    }

    public function addAlergia(Alergias $alergia): static
    {
        if (!$this->alergias->contains($alergia)) {
            $this->alergias->add($alergia);
            $alergia->addReaccione($this);
        }

        return $this;
    }

    public function removeAlergia(Alergias $alergia): static
    {
        if ($this->alergias->removeElement($alergia)) {
            $alergia->removeReaccione($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PacienteInmunizaciones>
     */
    public function getPacienteInmunizaciones(): Collection
    {
        return $this->pacienteInmunizaciones;
    }

    public function addPacienteInmunizacione(PacienteInmunizaciones $pacienteInmunizacione): static
    {
        if (!$this->pacienteInmunizaciones->contains($pacienteInmunizacione)) {
            $this->pacienteInmunizaciones->add($pacienteInmunizacione);
            $pacienteInmunizacione->addReaccione($this);
        }

        return $this;
    }

    public function removePacienteInmunizacione(PacienteInmunizaciones $pacienteInmunizacione): static
    {
        if ($this->pacienteInmunizaciones->removeElement($pacienteInmunizacione)) {
            $pacienteInmunizacione->removeReaccione($this);
        }

        return $this;
    }
}
