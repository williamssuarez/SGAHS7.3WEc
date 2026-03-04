<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\InmunizacionesAdministraciones;
use App\Enum\InmunizacionesTipos;
use App\Repository\InmunizacionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InmunizacionesRepository::class)]
class Inmunizaciones
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

    #[ORM\Column(enumType: InmunizacionesTipos::class)]
    private ?InmunizacionesTipos $tipo = null;

    #[ORM\Column(enumType: InmunizacionesAdministraciones::class)]
    private ?InmunizacionesAdministraciones $administracion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $frecuenciaSugerida = null;

    /**
     * @var Collection<int, PacienteInmunizaciones>
     */
    #[ORM\OneToMany(targetEntity: PacienteInmunizaciones::class, mappedBy: 'inmunizacion')]
    private Collection $pacienteInmunizaciones;

    public function __construct()
    {
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

    public function getTipo(): ?InmunizacionesTipos
    {
        return $this->tipo;
    }

    public function setTipo(InmunizacionesTipos $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getAdministracion(): ?InmunizacionesAdministraciones
    {
        return $this->administracion;
    }

    public function setAdministracion(InmunizacionesAdministraciones $administracion): static
    {
        $this->administracion = $administracion;

        return $this;
    }

    public function getFrecuenciaSugerida(): ?string
    {
        return $this->frecuenciaSugerida;
    }

    public function setFrecuenciaSugerida(?string $frecuenciaSugerida): static
    {
        $this->frecuenciaSugerida = $frecuenciaSugerida;

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
            $pacienteInmunizacione->setInmunizacion($this);
        }

        return $this;
    }

    public function removePacienteInmunizacione(PacienteInmunizaciones $pacienteInmunizacione): static
    {
        if ($this->pacienteInmunizaciones->removeElement($pacienteInmunizacione)) {
            // set the owning side to null (unless already changed)
            if ($pacienteInmunizacione->getInmunizacion() === $this) {
                $pacienteInmunizacione->setInmunizacion(null);
            }
        }

        return $this;
    }

    public function getInmunizacionesTiposBadgeConfig(): array
    {
        switch ($this->getTipo()) {
            case InmunizacionesTipos::I_VECTOR_VIRAL:
                return [
                    'class' => 'text-bg-primary',
                    'label' => InmunizacionesTipos::I_VECTOR_VIRAL->getReadableText()
                ];
            case InmunizacionesTipos::I_INACTIVE:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => InmunizacionesTipos::I_INACTIVE->getReadableText()
                ];
            case InmunizacionesTipos::I_TOXOIDE:
                return [
                    'class' => 'text-bg-info',
                    'label' => InmunizacionesTipos::I_TOXOIDE->getReadableText()
                ];
            case InmunizacionesTipos::I_ARNM:
                return [
                    'class' => 'text-bg-dark',
                    'label' => InmunizacionesTipos::I_ARNM->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getInmunizacionesAdministracionesBadgeConfig(): array
    {
        switch ($this->getAdministracion()) {
            case InmunizacionesAdministraciones::IM:
                return [
                    'class' => 'text-bg-primary',
                    'label' => InmunizacionesAdministraciones::IM->getReadableText()
                ];
            case InmunizacionesAdministraciones::SC:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => InmunizacionesAdministraciones::SC->getReadableText()
                ];
            case InmunizacionesAdministraciones::IDE:
                return [
                    'class' => 'text-bg-info',
                    'label' => InmunizacionesAdministraciones::IDE->getReadableText()
                ];
            case InmunizacionesAdministraciones::ORA:
                return [
                    'class' => 'text-bg-dark',
                    'label' => InmunizacionesAdministraciones::ORA->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
