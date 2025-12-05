<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\PacienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PacienteRepository::class)]
class Paciente
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellido = null;

    #[ORM\Column]
    private ?int $cedula = null;

    #[ORM\Column(length: 255)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $correo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $direccion = null;

    /**
     * @var Collection<int, Enfermedades>
     */
    #[ORM\ManyToMany(targetEntity: Enfermedades::class, inversedBy: 'pacientes')]
    private Collection $enfermedades;

    /**
     * @var Collection<int, Alergias>
     */
    #[ORM\ManyToMany(targetEntity: Alergias::class, inversedBy: 'pacientes')]
    private Collection $alergias;

    /**
     * @var Collection<int, Discapacidades>
     */
    #[ORM\ManyToMany(targetEntity: Discapacidades::class, inversedBy: 'pacientes')]
    private Collection $discapacidades;

    /**
     * @var Collection<int, Tratamientos>
     */
    #[ORM\ManyToMany(targetEntity: Tratamientos::class, inversedBy: 'pacientes')]
    private Collection $tratamientos;

    #[ORM\Column]
    private ?bool $hasMarcaPaso = null;

    public function __construct()
    {
        $this->enfermedades = new ArrayCollection();
        $this->alergias = new ArrayCollection();
        $this->discapacidades = new ArrayCollection();
        $this->tratamientos = new ArrayCollection();
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

    public function getCedula(): ?int
    {
        return $this->cedula;
    }

    public function setCedula(int $cedula): static
    {
        $this->cedula = $cedula;

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

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): static
    {
        $this->correo = $correo;

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

    /**
     * @return Collection<int, Enfermedades>
     */
    public function getEnfermedades(): Collection
    {
        return $this->enfermedades;
    }

    public function addEnfermedade(Enfermedades $enfermedade): static
    {
        if (!$this->enfermedades->contains($enfermedade)) {
            $this->enfermedades->add($enfermedade);
        }

        return $this;
    }

    public function removeEnfermedade(Enfermedades $enfermedade): static
    {
        $this->enfermedades->removeElement($enfermedade);

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
        }

        return $this;
    }

    public function removeAlergia(Alergias $alergia): static
    {
        $this->alergias->removeElement($alergia);

        return $this;
    }

    /**
     * @return Collection<int, Discapacidades>
     */
    public function getDiscapacidades(): Collection
    {
        return $this->discapacidades;
    }

    public function addDiscapacidade(Discapacidades $discapacidade): static
    {
        if (!$this->discapacidades->contains($discapacidade)) {
            $this->discapacidades->add($discapacidade);
        }

        return $this;
    }

    public function removeDiscapacidade(Discapacidades $discapacidade): static
    {
        $this->discapacidades->removeElement($discapacidade);

        return $this;
    }

    /**
     * @return Collection<int, Tratamientos>
     */
    public function getTratamientos(): Collection
    {
        return $this->tratamientos;
    }

    public function addTratamiento(Tratamientos $tratamiento): static
    {
        if (!$this->tratamientos->contains($tratamiento)) {
            $this->tratamientos->add($tratamiento);
        }

        return $this;
    }

    public function removeTratamiento(Tratamientos $tratamiento): static
    {
        $this->tratamientos->removeElement($tratamiento);

        return $this;
    }

    public function hasMarcaPaso(): ?bool
    {
        return $this->hasMarcaPaso;
    }

    public function setHasMarcaPaso(bool $hasMarcaPaso): static
    {
        $this->hasMarcaPaso = $hasMarcaPaso;

        return $this;
    }
}
