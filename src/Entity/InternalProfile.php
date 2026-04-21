<?php

namespace App\Entity;

use App\Repository\InternalProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InternalProfileRepository::class)]
class InternalProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellido = null;

    #[ORM\Column(length: 255)]
    private ?string $telefono = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoDocumento = null;

    #[ORM\Column(length: 255)]
    private ?string $nroDocumento = null;

    #[ORM\OneToOne(mappedBy: 'internalProfile', cascade: ['persist', 'remove'])]
    private ?User $webUser = null;

    /**
     * @var Collection<int, Especialidades>
     */
    #[ORM\ManyToMany(targetEntity: Especialidades::class)]
    private Collection $especialidades;

    /**
     * @var Collection<int, Cirugia>
     */
    #[ORM\OneToMany(targetEntity: Cirugia::class, mappedBy: 'cirujanoPrincipal')]
    private Collection $cirugias;

    /**
     * @var Collection<int, Cirugia>
     */
    #[ORM\OneToMany(targetEntity: Cirugia::class, mappedBy: 'anestesiologo')]
    private Collection $anestesiologo;

    public function __construct()
    {
        $this->especialidades = new ArrayCollection();
        $this->cirugias = new ArrayCollection();
        $this->anestesiologo = new ArrayCollection();
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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTipoDocumento(): ?string
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(string $tipoDocumento): static
    {
        $this->tipoDocumento = $tipoDocumento;

        return $this;
    }

    public function getNroDocumento(): ?string
    {
        return $this->nroDocumento;
    }

    public function setNroDocumento(string $nroDocumento): static
    {
        $this->nroDocumento = $nroDocumento;

        return $this;
    }

    public function getWebUser(): ?User
    {
        return $this->webUser;
    }

    public function setWebUser(?User $webUser): static
    {
        // unset the owning side of the relation if necessary
        if ($webUser === null && $this->webUser !== null) {
            $this->webUser->setInternalProfile(null);
        }

        // set the owning side of the relation if necessary
        if ($webUser !== null && $webUser->getInternalProfile() !== $this) {
            $webUser->setInternalProfile($this);
        }

        $this->webUser = $webUser;

        return $this;
    }

    /**
     * @return Collection<int, Especialidades>
     */
    public function getEspecialidades(): Collection
    {
        return $this->especialidades;
    }

    public function addEspecialidade(Especialidades $especialidade): static
    {
        if (!$this->especialidades->contains($especialidade)) {
            $this->especialidades->add($especialidade);
        }

        return $this;
    }

    public function removeEspecialidade(Especialidades $especialidade): static
    {
        $this->especialidades->removeElement($especialidade);

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
            $cirugia->setCirujanoPrincipal($this);
        }

        return $this;
    }

    public function removeCirugia(Cirugia $cirugia): static
    {
        if ($this->cirugias->removeElement($cirugia)) {
            // set the owning side to null (unless already changed)
            if ($cirugia->getCirujanoPrincipal() === $this) {
                $cirugia->setCirujanoPrincipal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cirugia>
     */
    public function getAnestesiologo(): Collection
    {
        return $this->anestesiologo;
    }

    public function addAnestesiologo(Cirugia $anestesiologo): static
    {
        if (!$this->anestesiologo->contains($anestesiologo)) {
            $this->anestesiologo->add($anestesiologo);
            $anestesiologo->setAnestesiologo($this);
        }

        return $this;
    }

    public function removeAnestesiologo(Cirugia $anestesiologo): static
    {
        if ($this->anestesiologo->removeElement($anestesiologo)) {
            // set the owning side to null (unless already changed)
            if ($anestesiologo->getAnestesiologo() === $this) {
                $anestesiologo->setAnestesiologo(null);
            }
        }

        return $this;
    }
}
