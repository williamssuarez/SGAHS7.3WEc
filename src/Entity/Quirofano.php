<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\QuirofanoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuirofanoRepository::class)]
class Quirofano
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    /**
     * @var Collection<int, Cirugia>
     */
    #[ORM\OneToMany(targetEntity: Cirugia::class, mappedBy: 'quirofano')]
    private Collection $cirugias;

    public function __construct()
    {
        $this->cirugias = new ArrayCollection();
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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, Cirugia>
     */
    public function getCirugias(): Collection
    {
        return $this->cirugias;
    }

    public function addCirugia(Cirugia $cirugia): static
    {
        if (!$this->cirugias->contains($cirugia)) {
            $this->cirugias->add($cirugia);
            $cirugia->setQuirofano($this);
        }

        return $this;
    }

    public function removeCirugia(Cirugia $cirugia): static
    {
        if ($this->cirugias->removeElement($cirugia)) {
            // set the owning side to null (unless already changed)
            if ($cirugia->getQuirofano() === $this) {
                $cirugia->setQuirofano(null);
            }
        }

        return $this;
    }
}
