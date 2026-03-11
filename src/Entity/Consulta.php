<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\ConsultaEstados;
use App\Enum\ConsultaTipos;
use App\Repository\ConsultaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;

#[ORM\Entity(repositoryClass: ConsultaRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Consulta
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consultas')]
    #[Gedmo\Versioned]
    private ?Paciente $paciente = null;

    #[ORM\Column(enumType: ConsultaTipos::class)]
    #[Gedmo\Versioned]
    private ?ConsultaTipos $tipoConsulta = null;

    #[ORM\Column(enumType: ConsultaEstados::class)]
    #[Gedmo\Versioned]
    private ?ConsultaEstados $estadoConsulta = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaInicio = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE, nullable: true)]
    #[Gedmo\Versioned]
    private ?\DateTime $fechaFin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $observacion = null;

    /**
     * @var Collection<int, Vitales>
     */
    #[ORM\OneToMany(targetEntity: Vitales::class, mappedBy: 'consulta')]
    private Collection $vitales;

    /**
     * @var Collection<int, Audit>
     */
    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'consulta')]
    private Collection $audits;

    public function __construct()
    {
        $this->vitales = new ArrayCollection();
        $this->audits = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Vitales>
     */
    public function getVitales(): Collection
    {
        return $this->vitales;
    }

    public function addVitale(Vitales $vitale): static
    {
        if (!$this->vitales->contains($vitale)) {
            $this->vitales->add($vitale);
            $vitale->setConsulta($this);
        }

        return $this;
    }

    public function removeVitale(Vitales $vitale): static
    {
        if ($this->vitales->removeElement($vitale)) {
            // set the owning side to null (unless already changed)
            if ($vitale->getConsulta() === $this) {
                $vitale->setConsulta(null);
            }
        }

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
            $audit->setConsulta($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): static
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getConsulta() === $this) {
                $audit->setConsulta(null);
            }
        }

        return $this;
    }
}
