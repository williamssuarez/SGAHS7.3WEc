<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\MedicamentosDosisTipos;
use App\Repository\MedicamentosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MedicamentosRepository::class)]
class Medicamentos
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

    #[ORM\Column(length: 255)]
    private ?string $nombreGenerico = null;

    #[ORM\Column(length: 255)]
    private ?string $concentracion = null;

    /**
     * @var Collection<int, Prescripciones>
     */
    #[ORM\OneToMany(targetEntity: Prescripciones::class, mappedBy: 'medicamento')]
    private Collection $prescripciones;

    public function __construct()
    {
        $this->prescripciones = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->getNombre(),
            $this->getNombreGenerico(),
            $this->getConcentracion(),
        );
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

    public function getNombreGenerico(): ?string
    {
        return $this->nombreGenerico;
    }

    public function setNombreGenerico(string $nombreGenerico): static
    {
        $this->nombreGenerico = $nombreGenerico;

        return $this;
    }

    public function getConcentracion(): ?string
    {
        return $this->concentracion;
    }

    public function setConcentracion(string $concentracion): static
    {
        $this->concentracion = $concentracion;

        return $this;
    }

    /**
     * @return Collection<int, Prescripciones>
     */
    public function getPrescripciones(): Collection
    {
        return $this->prescripciones;
    }

    public function addPrescripcione(Prescripciones $prescripcione): static
    {
        if (!$this->prescripciones->contains($prescripcione)) {
            $this->prescripciones->add($prescripcione);
            $prescripcione->setMedicamento($this);
        }

        return $this;
    }

    public function removePrescripcione(Prescripciones $prescripcione): static
    {
        if ($this->prescripciones->removeElement($prescripcione)) {
            // set the owning side to null (unless already changed)
            if ($prescripcione->getMedicamento() === $this) {
                $prescripcione->setMedicamento(null);
            }
        }

        return $this;
    }
}
