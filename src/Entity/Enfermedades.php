<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\EnfermedadesCategorias;
use App\Repository\EnfermedadesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnfermedadesRepository::class)]
class Enfermedades
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codigo = null;

    #[ORM\Column(enumType: EnfermedadesCategorias::class)]
    private ?EnfermedadesCategorias $categoria = null;

    /**
     * @var Collection<int, PacienteEnfermedades>
     */
    #[ORM\OneToMany(targetEntity: PacienteEnfermedades::class, mappedBy: 'enfermedad')]
    private Collection $pacienteEnfermedades;

    public function __construct()
    {
        $this->pacienteEnfermedades = new ArrayCollection();
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

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(?string $codigo): static
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getCategoria(): ?EnfermedadesCategorias
    {
        return $this->categoria;
    }

    public function setCategoria(EnfermedadesCategorias $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getEnfermedadesCategoriasBadgeConfig(): array
    {
        switch ($this->getCategoria()) {
            case EnfermedadesCategorias::E_MENTAL:
                return [
                    'class' => 'text-bg-primary',
                    'label' => EnfermedadesCategorias::E_MENTAL->getReadableText()
                ];
            case EnfermedadesCategorias::E_TRANSMITTABLE:
                return [
                    'class' => 'text-bg-warning',
                    'label' => EnfermedadesCategorias::E_TRANSMITTABLE->getReadableText()
                ];
            case EnfermedadesCategorias::E_GENETIC:
                return [
                    'class' => 'text-bg-info',
                    'label' => EnfermedadesCategorias::E_GENETIC->getReadableText()
                ];
            case EnfermedadesCategorias::E_NUTRITIONAL:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => EnfermedadesCategorias::E_NUTRITIONAL->getReadableText()
                ];
            case EnfermedadesCategorias::E_ONCOLOGICAL:
                return [
                    'class' => 'text-bg-danger',
                    'label' => EnfermedadesCategorias::E_ONCOLOGICAL->getReadableText()
                ];
            case EnfermedadesCategorias::E_CHRONIC:
                return [
                    'class' => 'text-bg-dark',
                    'label' => EnfermedadesCategorias::E_CHRONIC->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    /**
     * @return Collection<int, PacienteEnfermedades>
     */
    public function getPacienteEnfermedades(): Collection
    {
        return $this->pacienteEnfermedades;
    }

    public function addPacienteEnfermedade(PacienteEnfermedades $pacienteEnfermedade): static
    {
        if (!$this->pacienteEnfermedades->contains($pacienteEnfermedade)) {
            $this->pacienteEnfermedades->add($pacienteEnfermedade);
            $pacienteEnfermedade->setEnfermedad($this);
        }

        return $this;
    }

    public function removePacienteEnfermedade(PacienteEnfermedades $pacienteEnfermedade): static
    {
        if ($this->pacienteEnfermedades->removeElement($pacienteEnfermedade)) {
            // set the owning side to null (unless already changed)
            if ($pacienteEnfermedade->getEnfermedad() === $this) {
                $pacienteEnfermedade->setEnfermedad(null);
            }
        }

        return $this;
    }
}
