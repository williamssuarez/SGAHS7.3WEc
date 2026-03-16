<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\EmergenciasEstados;
use App\Repository\EmergenciaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmergenciaRepository::class)]
class Emergencia
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $fechaIngreso = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaEgreso = null;

    #[ORM\ManyToOne(inversedBy: 'emergencias')]
    private ?Paciente $paciente = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pacienteTemporal = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Triage $triage = null;

    #[ORM\ManyToOne(inversedBy: 'emergencias')]
    private ?Cama $camaActual = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\Column(enumType: EmergenciasEstados::class)]
    private ?EmergenciasEstados $estado = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->fechaIngreso = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaIngreso(): ?\DateTime
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(\DateTime $fechaIngreso): static
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getFechaEgreso(): ?\DateTime
    {
        return $this->fechaEgreso;
    }

    public function setFechaEgreso(?\DateTime $fechaEgreso): static
    {
        $this->fechaEgreso = $fechaEgreso;

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

    public function getPacienteTemporal(): ?string
    {
        return $this->pacienteTemporal;
    }

    public function setPacienteTemporal(?string $pacienteTemporal): static
    {
        $this->pacienteTemporal = $pacienteTemporal;

        return $this;
    }

    public function getTriage(): ?Triage
    {
        return $this->triage;
    }

    public function setTriage(?Triage $triage): static
    {
        $this->triage = $triage;

        return $this;
    }

    public function getCamaActual(): ?Cama
    {
        return $this->camaActual;
    }

    public function setCamaActual(?Cama $camaActual): static
    {
        $this->camaActual = $camaActual;

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

    public function getEstado(): ?EmergenciasEstados
    {
        return $this->estado;
    }

    public function setEstado(EmergenciasEstados $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
