<?php

namespace App\Entity;

use App\Repository\HistoriaPacienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriaPacienteRepository::class)]
class HistoriaPaciente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiaPacientes')]
    private ?User $doctor = null;

    #[ORM\ManyToOne(inversedBy: 'historiaPacientes')]
    private ?Paciente $paciente = null;

    #[ORM\Column]
    private ?float $latidos = null;

    #[ORM\Column(length: 255)]
    private ?string $tipoProcedimiento = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcionProdecimiento = null;

    /**
     * @var Collection<int, Medicamentos>
     */
    #[ORM\ManyToMany(targetEntity: Medicamentos::class)]
    private Collection $medicamentos;

    #[ORM\Column(length: 255)]
    private ?string $medicamentosObservaciones = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column]
    private ?\DateTime $fecha_atendido = null;

    public function __construct()
    {
        $this->medicamentos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): static
    {
        $this->doctor = $doctor;

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

    public function getLatidos(): ?float
    {
        return $this->latidos;
    }

    public function setLatidos(float $latidos): static
    {
        $this->latidos = $latidos;

        return $this;
    }

    public function getTipoProcedimiento(): ?string
    {
        return $this->tipoProcedimiento;
    }

    public function setTipoProcedimiento(string $tipoProcedimiento): static
    {
        $this->tipoProcedimiento = $tipoProcedimiento;

        return $this;
    }

    public function getDescripcionProdecimiento(): ?string
    {
        return $this->descripcionProdecimiento;
    }

    public function setDescripcionProdecimiento(string $descripcionProdecimiento): static
    {
        $this->descripcionProdecimiento = $descripcionProdecimiento;

        return $this;
    }

    /**
     * @return Collection<int, Medicamentos>
     */
    public function getMedicamentos(): Collection
    {
        return $this->medicamentos;
    }

    public function addMedicamento(Medicamentos $medicamento): static
    {
        if (!$this->medicamentos->contains($medicamento)) {
            $this->medicamentos->add($medicamento);
        }

        return $this;
    }

    public function removeMedicamento(Medicamentos $medicamento): static
    {
        $this->medicamentos->removeElement($medicamento);

        return $this;
    }

    public function getMedicamentosObservaciones(): ?string
    {
        return $this->medicamentosObservaciones;
    }

    public function setMedicamentosObservaciones(string $medicamentosObservaciones): static
    {
        $this->medicamentosObservaciones = $medicamentosObservaciones;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    public function getFechaAtendido(): ?\DateTime
    {
        return $this->fecha_atendido;
    }

    public function setFechaAtendido(\DateTime $fecha_atendido): static
    {
        $this->fecha_atendido = $fecha_atendido;

        return $this;
    }
}
