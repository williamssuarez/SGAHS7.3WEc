<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\TipoMovimientoInventario;
use App\Repository\MovimientoInventarioRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovimientoInventarioRepository::class)]
class MovimientoInventario
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'movimientoInventarios')]
    private ?InventarioLote $inventarioLote = null;

    #[ORM\Column(enumType: TipoMovimientoInventario::class)]
    private ?TipoMovimientoInventario $tipoMovimiento = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenciaOrigen = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventarioLote(): ?InventarioLote
    {
        return $this->inventarioLote;
    }

    public function setInventarioLote(?InventarioLote $inventarioLote): static
    {
        $this->inventarioLote = $inventarioLote;

        return $this;
    }

    public function getTipoMovimiento(): ?TipoMovimientoInventario
    {
        return $this->tipoMovimiento;
    }

    public function setTipoMovimiento(TipoMovimientoInventario $tipoMovimiento): static
    {
        $this->tipoMovimiento = $tipoMovimiento;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getReferenciaOrigen(): ?string
    {
        return $this->referenciaOrigen;
    }

    public function setReferenciaOrigen(?string $referenciaOrigen): static
    {
        $this->referenciaOrigen = $referenciaOrigen;

        return $this;
    }

    public function getTipoMovimientoBadgeConfig(): array
    {
        switch ($this->getTipoMovimiento()){
            case TipoMovimientoInventario::ENTRADA:
                return [
                    'class' => 'text-bg-success',
                    'label' => TipoMovimientoInventario::ENTRADA->getReadableText()
                ];
            case TipoMovimientoInventario::SALIDA:
                return [
                    'class' => 'text-bg-danger',
                    'label' => TipoMovimientoInventario::SALIDA->getReadableText()
                ];
            case TipoMovimientoInventario::AJUSTE:
                return [
                    'class' => 'text-bg-warning',
                    'label' => TipoMovimientoInventario::AJUSTE->getReadableText()
                ];
            case TipoMovimientoInventario::EDICION:
                return [
                    'class' => 'text-bg-primary',
                    'label' => TipoMovimientoInventario::EDICION->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
