<?php

namespace App\Entity;

use App\Enum\SangreTipos;
use App\Repository\ExternalProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExternalProfileRepository::class)]
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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Paciente $paciente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaNacimiento = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foto = null;

    #[ORM\Column(length: 255)]
    private ?string $sexo = null;

    #[ORM\Column(enumType: SangreTipos::class)]
    private ?SangreTipos $sangreTipo = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    /**
     * @var Collection<int, Audit>
     */
    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'externalProfile')]
    private Collection $audits;

    #[ORM\ManyToOne(inversedBy: 'externalProfiles')]
    private ?Sector $sector = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->audits = new ArrayCollection();
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

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTime
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(\DateTime $fechaNacimiento): static
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getSangreTipo(): ?SangreTipos
    {
        return $this->sangreTipo;
    }

    public function setSangreTipo(SangreTipos $sangreTipo): static
    {
        $this->sangreTipo = $sangreTipo;

        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

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
            $audit->setExternalProfile($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): static
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getExternalProfile() === $this) {
                $audit->setExternalProfile(null);
            }
        }

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): static
    {
        $this->sector = $sector;

        return $this;
    }
}
