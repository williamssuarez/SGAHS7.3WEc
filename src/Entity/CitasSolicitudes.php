<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CitasSolicitudesEstados;
use App\Repository\CitasSolicitudesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CitasSolicitudesRepository::class)]
class CitasSolicitudes
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'citasSolicitudes')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'citasSolicitudes')]
    private ?Especialidades $especialidad = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motivoConsulta = null;

    #[ORM\Column(enumType: CitasSolicitudesEstados::class)]
    private ?CitasSolicitudesEstados $estadoSolicitud = CitasSolicitudesEstados::PENDING;

    #[ORM\Column(nullable: true)]
    private ?int $scorePrioridad = null;

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

    public function getMotivoConsulta(): ?string
    {
        return $this->motivoConsulta;
    }

    public function setMotivoConsulta(string $motivoConsulta): static
    {
        $this->motivoConsulta = $motivoConsulta;

        return $this;
    }

    public function getEstadoSolicitud(): ?CitasSolicitudesEstados
    {
        return $this->estadoSolicitud;
    }

    public function setEstadoSolicitud(CitasSolicitudesEstados $estadoSolicitud): static
    {
        $this->estadoSolicitud = $estadoSolicitud;

        return $this;
    }

    public function getScorePrioridad(): ?int
    {
        return $this->scorePrioridad;
    }

    public function setScorePrioridad(?int $scorePrioridad): static
    {
        $this->scorePrioridad = $scorePrioridad;

        return $this;
    }

    public function getCitasSolicitudesEstadosBadgeConfig(): array
    {
        switch ($this->getEstadoSolicitud()) {
            case CitasSolicitudesEstados::PENDING:
                return [
                    'class' => 'text-bg-warning',
                    'label' => CitasSolicitudesEstados::PENDING->getReadableText()
                ];
            case CitasSolicitudesEstados::PROCESSING:
                return [
                    'class' => 'text-bg-primary',
                    'label' => CitasSolicitudesEstados::PROCESSING->getReadableText()
                ];
            case CitasSolicitudesEstados::SCHEDULED:
                return [
                    'class' => 'text-bg-success',
                    'label' => CitasSolicitudesEstados::SCHEDULED->getReadableText()
                ];
            case CitasSolicitudesEstados::REJECTED:
                return [
                    'class' => 'text-bg-danger',
                    'label' => CitasSolicitudesEstados::REJECTED->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }
}
