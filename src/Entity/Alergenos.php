<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\AlergenosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlergenosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Alergenos
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

    /**
     * @var Collection<int, Alergias>
     */
    #[ORM\OneToMany(targetEntity: Alergias::class, mappedBy: 'alergeno')]
    private Collection $alergias;

    public function __construct()
    {
        $this->alergias = new ArrayCollection();
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

    /**
     * @return Collection<int, Alergias>
     */
    public function getAlergias(): Collection
    {
        return $this->alergias;
    }

    public function addAlergia(Alergias $alergia): static
    {
        if (!$this->alergias->contains($alergia)) {
            $this->alergias->add($alergia);
            $alergia->setAlergeno($this);
        }

        return $this;
    }

    public function removeAlergia(Alergias $alergia): static
    {
        if ($this->alergias->removeElement($alergia)) {
            // set the owning side to null (unless already changed)
            if ($alergia->getAlergeno() === $this) {
                $alergia->setAlergeno(null);
            }
        }

        return $this;
    }
}
