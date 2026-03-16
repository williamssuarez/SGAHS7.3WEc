<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CamaEstados;
use App\Repository\CamaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CamaRepository::class)]
class Cama
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codigo = null;

    #[ORM\Column(enumType: CamaEstados::class)]
    private ?CamaEstados $estado = null;

    /**
     * @var Collection<int, Emergencia>
     */
    #[ORM\OneToMany(targetEntity: Emergencia::class, mappedBy: 'camaActual')]
    private Collection $emergencias;

    #[ORM\ManyToOne(inversedBy: 'camas')]
    private ?ZonaCama $zona = null;

    public function __construct()
    {
        $this->emergencias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getEstado(): ?CamaEstados
    {
        return $this->estado;
    }

    public function setEstado(CamaEstados $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, Emergencia>
     */
    public function getEmergencias(): Collection
    {
        return $this->emergencias;
    }

    public function addEmergencia(Emergencia $emergencia): static
    {
        if (!$this->emergencias->contains($emergencia)) {
            $this->emergencias->add($emergencia);
            $emergencia->setCamaActual($this);
        }

        return $this;
    }

    public function removeEmergencia(Emergencia $emergencia): static
    {
        if ($this->emergencias->removeElement($emergencia)) {
            // set the owning side to null (unless already changed)
            if ($emergencia->getCamaActual() === $this) {
                $emergencia->setCamaActual(null);
            }
        }

        return $this;
    }

    public function getZona(): ?ZonaCama
    {
        return $this->zona;
    }

    public function setZona(?ZonaCama $zona): static
    {
        $this->zona = $zona;

        return $this;
    }

    public function getEstadosBadgeConfig(): array
    {
        switch ($this->getEstado()) {
            case CamaEstados::AVAILABLE:
                return [
                    'class' => 'text-bg-success',
                    'label' => CamaEstados::AVAILABLE->getReadableText()
                ];
            case CamaEstados::OCUPIED:
                return [
                    'class' => 'text-bg-primary',
                    'label' => CamaEstados::OCUPIED->getReadableText()
                ];
            case CamaEstados::CLEANING:
                return [
                    'class' => 'text-bg-warning',
                    'label' => CamaEstados::CLEANING->getReadableText()
                ];
            case CamaEstados::MAINTENANCE:
                return [
                    'class' => 'text-bg-danger',
                    'label' => CamaEstados::MAINTENANCE->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
