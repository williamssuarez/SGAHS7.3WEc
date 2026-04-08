<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CamaEstados;
use App\Repository\CamaHospitalizacionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CamaHospitalizacionRepository::class)]
class HospitalizacionCama
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codigo = null;

    #[ORM\ManyToOne(inversedBy: 'camasHospitalizaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Habitacion $habitacion = null;

    #[ORM\Column(enumType: CamaEstados::class)]
    private ?CamaEstados $estado = null;

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

    public function getHabitacion(): ?Habitacion
    {
        return $this->habitacion;
    }

    public function setHabitacion(?Habitacion $habitacion): static
    {
        $this->habitacion = $habitacion;

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
