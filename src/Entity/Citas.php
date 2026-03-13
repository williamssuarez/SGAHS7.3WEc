<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CitasEstados;
use App\Repository\CitasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CitasRepository::class)]
class Citas
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'citas')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'citas')]
    private ?Especialidades $especialidad = null;

    #[ORM\ManyToOne(inversedBy: 'citas')]
    private ?Consultorios $consultorio = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fecha = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaInicio = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaFin = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?CitasSolicitudes $solicitud = null;

    #[ORM\OneToOne(targetEntity: Consulta::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Consulta $consulta = null;

    #[ORM\Column(enumType: CitasEstados::class)]
    private ?CitasEstados $estadoCita = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEspecialidad(): ?Especialidades
    {
        return $this->especialidad;
    }

    public function setEspecialidad(?Especialidades $especialidad): static
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    public function getConsultorio(): ?Consultorios
    {
        return $this->consultorio;
    }

    public function setConsultorio(?Consultorios $consultorio): static
    {
        $this->consultorio = $consultorio;

        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
    }

    public function setFecha(\DateTime $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getHoraInicio(): ?\DateTime
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(\DateTime $horaInicio): static
    {
        $this->horaInicio = $horaInicio;

        return $this;
    }

    public function getHoraFin(): ?\DateTime
    {
        return $this->horaFin;
    }

    public function setHoraFin(\DateTime $horaFin): static
    {
        $this->horaFin = $horaFin;

        return $this;
    }

    public function getSolicitud(): ?CitasSolicitudes
    {
        return $this->solicitud;
    }

    public function setSolicitud(?CitasSolicitudes $solicitud): static
    {
        $this->solicitud = $solicitud;

        return $this;
    }

    public function getConsulta(): ?Consulta
    {
        return $this->consulta;
    }

    public function setConsulta(?Consulta $consulta): static
    {
        $this->consulta = $consulta;

        return $this;
    }

    public function getEstadoCita(): ?CitasEstados
    {
        return $this->estadoCita;
    }

    public function setEstadoCita(CitasEstados $estadoCita): static
    {
        $this->estadoCita = $estadoCita;

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
}
