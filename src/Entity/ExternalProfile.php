<?php

namespace App\Entity;

use App\Repository\ExternalProfileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExternalProfileRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NRODOCUMENTO_EXTERNAL', fields: ['nroDocumento'])]
class ExternalProfile
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoDocumento = null;

    #[ORM\Column(length: 255)]
    private ?string $nroDocumento = null;

    #[ORM\OneToOne(mappedBy: 'externalProfile', cascade: ['persist', 'remove'])]
    private ?User $webUser = null;

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

    public function setDireccion(?string $direccion): static
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
            $this->webUser->setExternalProfile(null);
        }

        // set the owning side of the relation if necessary
        if ($webUser !== null && $webUser->getExternalProfile() !== $this) {
            $webUser->setExternalProfile($this);
        }

        $this->webUser = $webUser;

        return $this;
    }
}
