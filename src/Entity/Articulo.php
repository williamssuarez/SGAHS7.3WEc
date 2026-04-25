<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\ArticuloCategoria;
use App\Repository\ArticuloRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticuloRepository::class)]
class Articulo
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $codigoBarras = null;

    #[ORM\Column(enumType: ArticuloCategoria::class)]
    private ?ArticuloCategoria $categoria = null;

    #[ORM\Column(length: 255)]
    private ?string $unidadMedida = null;

    #[ORM\Column]
    private ?int $stockMinimo = null;

    /**
     * @var Collection<int, InventarioLote>
     */
    #[ORM\OneToMany(targetEntity: InventarioLote::class, mappedBy: 'articulo')]
    private Collection $inventarioLotes;

    public function __construct()
    {
        $this->inventarioLotes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nombre;
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

    public function getCodigoBarras(): ?string
    {
        return $this->codigoBarras;
    }

    public function setCodigoBarras(string $codigoBarras): static
    {
        $this->codigoBarras = $codigoBarras;

        return $this;
    }

    public function getCategoria(): ?ArticuloCategoria
    {
        return $this->categoria;
    }

    public function setCategoria(ArticuloCategoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getUnidadMedida(): ?string
    {
        return $this->unidadMedida;
    }

    public function setUnidadMedida(string $unidadMedida): static
    {
        $this->unidadMedida = $unidadMedida;

        return $this;
    }

    public function getStockMinimo(): ?int
    {
        return $this->stockMinimo;
    }

    public function setStockMinimo(int $stockMinimo): static
    {
        $this->stockMinimo = $stockMinimo;

        return $this;
    }

    /**
     * @return Collection<int, InventarioLote>
     */
    public function getInventarioLotes(): Collection
    {
        return $this->inventarioLotes;
    }

    public function addInventarioLote(InventarioLote $inventarioLote): static
    {
        if (!$this->inventarioLotes->contains($inventarioLote)) {
            $this->inventarioLotes->add($inventarioLote);
            $inventarioLote->setArticulo($this);
        }

        return $this;
    }

    public function removeInventarioLote(InventarioLote $inventarioLote): static
    {
        if ($this->inventarioLotes->removeElement($inventarioLote)) {
            // set the owning side to null (unless already changed)
            if ($inventarioLote->getArticulo() === $this) {
                $inventarioLote->setArticulo(null);
            }
        }

        return $this;
    }
}
