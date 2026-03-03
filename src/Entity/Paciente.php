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
    private ?string $cedula = null;

    #[ORM\Column(length: 255)]
    private ?string $telefono = null;

    #[ORM\Column(length: 255)]
    private ?string $correo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $direccion = null;

    /**
     * @var Collection<int, Tratamientos>
     */
    #[ORM\ManyToMany(targetEntity: Tratamientos::class, inversedBy: 'pacientes')]
    private Collection $tratamientos;

    #[ORM\Column]
    private ?bool $hasMarcaPaso = null;

    /**
     * @var Collection<int, HistoriaPaciente>
     */
    #[ORM\OneToMany(targetEntity: HistoriaPaciente::class, mappedBy: 'paciente')]
    private Collection $historiaPacientes;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fechaNacimiento = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foto = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $sexo;

    #[ORM\Column]
    private ?bool $fallecido = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoDocumento = null;

    /**
     * @var Collection<int, Attachments>
     */
    #[ORM\OneToMany(targetEntity: Attachments::class, mappedBy: 'paciente')]
    private Collection $attachments;

    /**
     * @var Collection<int, Alergias>
     */
    #[ORM\OneToMany(targetEntity: Alergias::class, mappedBy: 'paciente')]
    private Collection $alergias;

    /**
     * @var Collection<int, Consulta>
     */
    #[ORM\OneToMany(targetEntity: Consulta::class, mappedBy: 'paciente')]
    private Collection $consultas;

    /**
     * @var Collection<int, PacienteCondiciones>
     */
    #[ORM\OneToMany(targetEntity: PacienteCondiciones::class, mappedBy: 'paciente')]
    private Collection $pacienteCondiciones;

    /**
     * @var Collection<int, PacienteEnfermedades>
     */
    #[ORM\OneToMany(targetEntity: PacienteEnfermedades::class, mappedBy: 'paciente')]
    private Collection $pacienteEnfermedades;

    /**
     * @var Collection<int, PacienteDiscapacidades>
     */
    #[ORM\OneToMany(targetEntity: PacienteDiscapacidades::class, mappedBy: 'paciente')]
    private Collection $pacienteDiscapacidades;

    public function __construct()
    {
        $this->tratamientos = new ArrayCollection();
        $this->historiaPacientes = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->alergias = new ArrayCollection();
        $this->consultas = new ArrayCollection();
        $this->pacienteCondiciones = new ArrayCollection();
        $this->pacienteEnfermedades = new ArrayCollection();
        $this->pacienteDiscapacidades = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s (%s-%s)',
            $this->getNombre(),
            $this->getApellido(),
            $this->getTipoDocumento(),
            number_format($this->getCedula(), 0, ',', '.')
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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getCedula(): ?string
    {
        return $this->cedula;
    }

    public function setCedula(string $cedula): static
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

    /**
     * @return Collection<int, HistoriaPaciente>
     */
    public function getHistoriaPacientes(): Collection
    {
        return $this->historiaPacientes;
    }

    public function addHistoriaPaciente(HistoriaPaciente $historiaPaciente): static
    {
        if (!$this->historiaPacientes->contains($historiaPaciente)) {
            $this->historiaPacientes->add($historiaPaciente);
            $historiaPaciente->setPaciente($this);
        }

        return $this;
    }

    public function removeHistoriaPaciente(HistoriaPaciente $historiaPaciente): static
    {
        if ($this->historiaPacientes->removeElement($historiaPaciente)) {
            // set the owning side to null (unless already changed)
            if ($historiaPaciente->getPaciente() === $this) {
                $historiaPaciente->setPaciente(null);
            }
        }

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTime
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(?\DateTime $fechaNacimiento): static
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

    public function isFallecido(): ?bool
    {
        return $this->fallecido;
    }

    public function setFallecido(bool $fallecido): static
    {
        $this->fallecido = $fallecido;

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

    /**
     * @return Collection<int, Attachments>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachments $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setPaciente($this);
        }

        return $this;
    }

    public function removeAttachment(Attachments $attachment): static
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getPaciente() === $this) {
                $attachment->setPaciente(null);
            }
        }

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
            $alergia->setPaciente($this);
        }

        return $this;
    }

    public function removeAlergia(Alergias $alergia): static
    {
        if ($this->alergias->removeElement($alergia)) {
            // set the owning side to null (unless already changed)
            if ($alergia->getPaciente() === $this) {
                $alergia->setPaciente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consulta>
     */
    public function getConsultas(): Collection
    {
        return $this->consultas;
    }

    public function addConsulta(Consulta $consulta): static
    {
        if (!$this->consultas->contains($consulta)) {
            $this->consultas->add($consulta);
            $consulta->setPaciente($this);
        }

        return $this;
    }

    public function removeConsulta(Consulta $consulta): static
    {
        if ($this->consultas->removeElement($consulta)) {
            // set the owning side to null (unless already changed)
            if ($consulta->getPaciente() === $this) {
                $consulta->setPaciente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacienteCondiciones>
     */
    public function getPacienteCondiciones(): Collection
    {
        return $this->pacienteCondiciones;
    }

    public function addPacienteCondicione(PacienteCondiciones $pacienteCondicione): static
    {
        if (!$this->pacienteCondiciones->contains($pacienteCondicione)) {
            $this->pacienteCondiciones->add($pacienteCondicione);
            $pacienteCondicione->setPaciente($this);
        }

        return $this;
    }

    public function removePacienteCondicione(PacienteCondiciones $pacienteCondicione): static
    {
        if ($this->pacienteCondiciones->removeElement($pacienteCondicione)) {
            // set the owning side to null (unless already changed)
            if ($pacienteCondicione->getPaciente() === $this) {
                $pacienteCondicione->setPaciente(null);
            }
        }

        return $this;
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
            $pacienteEnfermedade->setPaciente($this);
        }

        return $this;
    }

    public function removePacienteEnfermedade(PacienteEnfermedades $pacienteEnfermedade): static
    {
        if ($this->pacienteEnfermedades->removeElement($pacienteEnfermedade)) {
            // set the owning side to null (unless already changed)
            if ($pacienteEnfermedade->getPaciente() === $this) {
                $pacienteEnfermedade->setPaciente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacienteDiscapacidades>
     */
    public function getPacienteDiscapacidades(): Collection
    {
        return $this->pacienteDiscapacidades;
    }

    public function addPacienteDiscapacidade(PacienteDiscapacidades $pacienteDiscapacidade): static
    {
        if (!$this->pacienteDiscapacidades->contains($pacienteDiscapacidade)) {
            $this->pacienteDiscapacidades->add($pacienteDiscapacidade);
            $pacienteDiscapacidade->setPaciente($this);
        }

        return $this;
    }

    public function removePacienteDiscapacidade(PacienteDiscapacidades $pacienteDiscapacidade): static
    {
        if ($this->pacienteDiscapacidades->removeElement($pacienteDiscapacidade)) {
            // set the owning side to null (unless already changed)
            if ($pacienteDiscapacidade->getPaciente() === $this) {
                $pacienteDiscapacidade->setPaciente(null);
            }
        }

        return $this;
    }
}
