<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\InventarioLoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventarioLoteRepository::class)]
class InventarioLote
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventarioLotes')]
    private ?Articulo $articulo = null;

    #[ORM\Column(length: 255)]
    private ?string $lote = null;

    #[ORM\Column]
    private ?\DateTime $fechaCaducidad = null;

    #[ORM\Column]
    private ?int $cantidadActual = null;

    #[ORM\Column]
    private ?float $precioCompra = null;

    #[ORM\Column]
    private ?float $precioVenta = null;

    /**
     * @var Collection<int, MovimientoInventario>
     */
    #[ORM\OneToMany(targetEntity: MovimientoInventario::class, mappedBy: 'inventarioLote')]
    private Collection $movimientoInventarios;

    /**
     * @var Collection<int, Audit>
     */
    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'inventarioLote')]
    private Collection $audits;

    public function __construct()
    {
        $this->movimientoInventarios = new ArrayCollection();
        $this->audits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticulo(): ?Articulo
    {
        return $this->articulo;
    }

    public function setArticulo(?Articulo $articulo): static
    {
        $this->articulo = $articulo;

        return $this;
    }

    public function getLote(): ?string
    {
        return $this->lote;
    }

    public function setLote(string $lote): static
    {
        $this->lote = $lote;

        return $this;
    }

    public function getFechaCaducidad(): ?\DateTime
    {
        return $this->fechaCaducidad;
    }

    public function setFechaCaducidad(\DateTime $fechaCaducidad): static
    {
        $this->fechaCaducidad = $fechaCaducidad;

        return $this;
    }

    public function getCantidadActual(): ?int
    {
        return $this->cantidadActual;
    }

    public function setCantidadActual(int $cantidadActual): static
    {
        $this->cantidadActual = $cantidadActual;

        return $this;
    }

    public function getPrecioCompra(): ?float
    {
        return $this->precioCompra;
    }

    public function setPrecioCompra(float $precioCompra): static
    {
        $this->precioCompra = $precioCompra;

        return $this;
    }

    public function getPrecioVenta(): ?float
    {
        return $this->precioVenta;
    }

    public function setPrecioVenta(float $precioVenta): static
    {
        $this->precioVenta = $precioVenta;

        return $this;
    }

    /**
     * @return Collection<int, MovimientoInventario>
     */
    public function getMovimientoInventarios(): Collection
    {
        return $this->movimientoInventarios;
    }

    public function addMovimientoInventario(MovimientoInventario $movimientoInventario): static
    {
        if (!$this->movimientoInventarios->contains($movimientoInventario)) {
            $this->movimientoInventarios->add($movimientoInventario);
            $movimientoInventario->setInventarioLote($this);
        }

        return $this;
    }

    public function removeMovimientoInventario(MovimientoInventario $movimientoInventario): static
    {
        if ($this->movimientoInventarios->removeElement($movimientoInventario)) {
            // set the owning side to null (unless already changed)
            if ($movimientoInventario->getInventarioLote() === $this) {
                $movimientoInventario->setInventarioLote(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Audit>
     */
    public function getAudits(): Collection
    {
        return $this->audits;
    }

    public function addAudit(Audit $audit): static
    {
        if (!$this->audits->contains($audit)) {
            $this->audits->add($audit);
            $audit->setInventarioLote($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): static
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getInventarioLote() === $this) {
                $audit->setInventarioLote(null);
            }
        }

        return $this;
    }
}
