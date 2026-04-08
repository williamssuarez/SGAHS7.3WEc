<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\IndicacionMedicaEstado;
use App\Enum\IndicacionMedicaTipo;
use App\Enum\InmunizacionesAdministraciones;
use App\Repository\IndicacionMedicaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IndicacionMedicaRepository::class)]
class IndicacionMedica
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'indicacionMedicas')]
    private ?Hospitalizaciones $hospitalizacion = null;

    #[ORM\Column(enumType: IndicacionMedicaTipo::class)]
    private ?IndicacionMedicaTipo $tipo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $frecuencia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $viaAdministracion = null;

    #[ORM\Column(enumType: IndicacionMedicaEstado::class)]
    private ?IndicacionMedicaEstado $estado = null;

    /**
     * @var Collection<int, KardexEnfermeria>
     */
    #[ORM\OneToMany(targetEntity: KardexEnfermeria::class, mappedBy: 'indicacionMedica')]
    private Collection $kardexEnfermerias;

    public function __construct()
    {
        $this->kardexEnfermerias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHospitalizacion(): ?Hospitalizaciones
    {
        return $this->hospitalizacion;
    }

    public function setHospitalizacion(?Hospitalizaciones $hospitalizacion): static
    {
        $this->hospitalizacion = $hospitalizacion;

        return $this;
    }

    public function getTipo(): ?IndicacionMedicaTipo
    {
        return $this->tipo;
    }

    public function setTipo(IndicacionMedicaTipo $tipo): static
    {
        $this->tipo = $tipo;

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

    public function getFrecuencia(): ?string
    {
        return $this->frecuencia;
    }

    public function setFrecuencia(string $frecuencia): static
    {
        $this->frecuencia = $frecuencia;

        return $this;
    }

    public function getViaAdministracion(): ?string
    {
        return $this->viaAdministracion;
    }

    public function setViaAdministracion(?string $viaAdministracion): static
    {
        $this->viaAdministracion = $viaAdministracion;

        return $this;
    }

    public function getEstado(): ?IndicacionMedicaEstado
    {
        return $this->estado;
    }

    public function setEstado(IndicacionMedicaEstado $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, KardexEnfermeria>
     */
    public function getKardexEnfermerias(): Collection
    {
        return $this->kardexEnfermerias;
    }

    public function addKardexEnfermeria(KardexEnfermeria $kardexEnfermeria): static
    {
        if (!$this->kardexEnfermerias->contains($kardexEnfermeria)) {
            $this->kardexEnfermerias->add($kardexEnfermeria);
            $kardexEnfermeria->setIndicacionMedica($this);
        }

        return $this;
    }

    public function removeKardexEnfermeria(KardexEnfermeria $kardexEnfermeria): static
    {
        if ($this->kardexEnfermerias->removeElement($kardexEnfermeria)) {
            // set the owning side to null (unless already changed)
            if ($kardexEnfermeria->getIndicacionMedica() === $this) {
                $kardexEnfermeria->setIndicacionMedica(null);
            }
        }

        return $this;
    }
}
