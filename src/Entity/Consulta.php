<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\ConsultaEstados;
use App\Enum\ConsultaTipos;
use App\Repository\ConsultaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultaRepository::class)]
class Consulta
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consultas')]
    private ?Paciente $paciente = null;

    #[ORM\Column(enumType: ConsultaTipos::class)]
    private ?ConsultaTipos $tipoConsulta = null;

    #[ORM\Column(enumType: ConsultaEstados::class)]
    private ?ConsultaEstados $estadoConsulta = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTime $fechaInicio = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    private ?\DateTime $fechaFin = null;

    #[ORM\OneToOne(inversedBy: 'consulta', cascade: ['persist', 'remove'])]
    private ?Vitales $vitales = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observacion = null;

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

    public function getTipoConsulta(): ?ConsultaTipos
    {
        return $this->tipoConsulta;
    }

    public function setTipoConsulta(ConsultaTipos $tipoConsulta): static
    {
        $this->tipoConsulta = $tipoConsulta;

        return $this;
    }

    public function getEstadoConsulta(): ?ConsultaEstados
    {
        return $this->estadoConsulta;
    }

    public function setEstadoConsulta(ConsultaEstados $estadoConsulta): static
    {
        $this->estadoConsulta = $estadoConsulta;

        return $this;
    }

    public function getFechaInicio(): ?\DateTime
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTime $fechaInicio): static
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTime
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTime $fechaFin): static
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getVitales(): ?Vitales
    {
        return $this->vitales;
    }

    public function setVitales(?Vitales $vitales): static
    {
        $this->vitales = $vitales;

        return $this;
    }

    public function getConsultaTiposBadgeConfig(): array
    {
        switch ($this->getTipoConsulta()){
            case ConsultaTipos::CT_GENERAL:
                return [
                    'class' => 'text-bg-primary',
                    'label' => ConsultaTipos::CT_GENERAL->getReadableText()
                ];
            case ConsultaTipos::CT_ESPECIALIDAD:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => ConsultaTipos::CT_ESPECIALIDAD->getReadableText()
                ];
            case ConsultaTipos::CT_SEGUIMIENTO:
                return [
                    'class' => 'text-bg-dark',
                    'label' => ConsultaTipos::CT_SEGUIMIENTO->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getConsultaEstadosBadgeConfig(): array
    {
        switch ($this->getEstadoConsulta()){
            case ConsultaEstados::PENDING:
                return [
                    'class' => 'text-bg-warning',
                    'label' => ConsultaEstados::PENDING->getReadableText()
                ];
            case ConsultaEstados::ACTIVE:
                return [
                    'class' => 'text-bg-primary',
                    'label' => ConsultaEstados::ACTIVE->getReadableText()
                ];
            case ConsultaEstados::FINISHED:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => ConsultaEstados::FINISHED->getReadableText()
                ];
            case ConsultaEstados::CANCELED:
                return [
                    'class' => 'text-bg-danger',
                    'label' => ConsultaEstados::CANCELED->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): static
    {
        $this->observacion = $observacion;

        return $this;
    }
}
