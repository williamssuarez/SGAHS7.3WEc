<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\InmunizacionesTipos;
use App\Enum\PacienteInmunizacionesDosis;
use App\Repository\PacienteInmunizacionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PacienteInmunizacionesRepository::class)]
class PacienteInmunizaciones
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteInmunizaciones')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'pacienteInmunizaciones')]
    private ?Inmunizaciones $inmunizacion = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaAplicacion = null;

    #[ORM\Column(length: 255)]
    private ?string $sitioAplicacion = null;

    #[ORM\Column(length: 255)]
    private ?string $fabricante = null;

    #[ORM\Column(enumType: PacienteInmunizacionesDosis::class)]
    private ?PacienteInmunizacionesDosis $dosis = null;

    /**
     * @var Collection<int, Reacciones>
     */
    #[ORM\ManyToMany(targetEntity: Reacciones::class, inversedBy: 'pacienteInmunizaciones')]
    private Collection $reacciones;

    public function __construct()
    {
        $this->reacciones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }

    public function getInmunizacion(): ?Inmunizaciones
    {
        return $this->inmunizacion;
    }

    public function setInmunizacion(?Inmunizaciones $inmunizacion): static
    {
        $this->inmunizacion = $inmunizacion;

        return $this;
    }

    public function getFechaAplicacion(): ?\DateTime
    {
        return $this->fechaAplicacion;
    }

    public function setFechaAplicacion(\DateTime $fechaAplicacion): static
    {
        $this->fechaAplicacion = $fechaAplicacion;

        return $this;
    }

    public function getSitioAplicacion(): ?string
    {
        return $this->sitioAplicacion;
    }

    public function setSitioAplicacion(string $sitioAplicacion): static
    {
        $this->sitioAplicacion = $sitioAplicacion;

        return $this;
    }

    public function getFabricante(): ?string
    {
        return $this->fabricante;
    }

    public function setFabricante(string $fabricante): static
    {
        $this->fabricante = $fabricante;

        return $this;
    }

    public function getDosis(): ?PacienteInmunizacionesDosis
    {
        return $this->dosis;
    }

    public function setDosis(PacienteInmunizacionesDosis $dosis): static
    {
        $this->dosis = $dosis;

        return $this;
    }

    /**
     * @return Collection<int, Reacciones>
     */
    public function getReacciones(): Collection
    {
        return $this->reacciones;
    }

    public function addReaccione(Reacciones $reaccione): static
    {
        if (!$this->reacciones->contains($reaccione)) {
            $this->reacciones->add($reaccione);
        }

        return $this;
    }

    public function removeReaccione(Reacciones $reaccione): static
    {
        $this->reacciones->removeElement($reaccione);

        return $this;
    }

    public function getPacienteInmunizacionesDosisBadgeConfig(): array
    {
        switch ($this->getDosis()) {
            case PacienteInmunizacionesDosis::FIRST:
                return [
                    'class' => 'text-bg-primary',
                    'label' => PacienteInmunizacionesDosis::FIRST->getReadableText()
                ];
            case PacienteInmunizacionesDosis::SECOND:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => PacienteInmunizacionesDosis::SECOND->getReadableText()
                ];
            case PacienteInmunizacionesDosis::THIRD:
                return [
                    'class' => 'text-bg-info',
                    'label' => PacienteInmunizacionesDosis::THIRD->getReadableText()
                ];
            case PacienteInmunizacionesDosis::BOOSTER:
                return [
                    'class' => 'text-bg-dark',
                    'label' => PacienteInmunizacionesDosis::BOOSTER->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
