<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CirugiaEstados;
use App\Enum\EmergenciasEstados;
use App\Repository\CirugiaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CirugiaRepository::class)]
class Cirugia
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    private ?Emergencia $emergenciaOrigen = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    private ?Hospitalizaciones $hospitalizacionOrigen = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    private ?Consulta $consultaOrigen = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    private ?Quirofano $quirofano = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InternalProfile $cirujanoPrincipal = null;

    #[ORM\ManyToOne(inversedBy: 'anestesiologo')]
    private ?InternalProfile $anestesiologo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $procedimientoPropuesto = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnosticoPreoperatorio = null;

    #[ORM\Column(enumType: CirugiaEstados::class)]
    private ?CirugiaEstados $estado = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lateralidad = null;

    #[ORM\Column]
    private ?\DateTime $fechaHoraProgramada = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaIngresoSala = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaInicioAnestesia = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaIncision = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaCierre = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaSalidaSala = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motivoCancelacion = null;

    /**
     * @var Collection<int, Audit>
     */
    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'cirugia')]
    private Collection $audits;

    public function __construct()
    {
        $this->audits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmergenciaOrigen(): ?Emergencia
    {
        return $this->emergenciaOrigen;
    }

    public function setEmergenciaOrigen(?Emergencia $emergenciaOrigen): static
    {
        $this->emergenciaOrigen = $emergenciaOrigen;

        return $this;
    }

    public function getHospitalizacionOrigen(): ?Hospitalizaciones
    {
        return $this->hospitalizacionOrigen;
    }

    public function setHospitalizacionOrigen(?Hospitalizaciones $hospitalizacionOrigen): static
    {
        $this->hospitalizacionOrigen = $hospitalizacionOrigen;

        return $this;
    }

    public function getConsultaOrigen(): ?Consulta
    {
        return $this->consultaOrigen;
    }

    public function setConsultaOrigen(?Consulta $consultaOrigen): static
    {
        $this->consultaOrigen = $consultaOrigen;

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

    public function getQuirofano(): ?Quirofano
    {
        return $this->quirofano;
    }

    public function setQuirofano(?Quirofano $quirofano): static
    {
        $this->quirofano = $quirofano;

        return $this;
    }

    public function getCirujanoPrincipal(): ?InternalProfile
    {
        return $this->cirujanoPrincipal;
    }

    public function setCirujanoPrincipal(?InternalProfile $cirujanoPrincipal): static
    {
        $this->cirujanoPrincipal = $cirujanoPrincipal;

        return $this;
    }

    public function getAnestesiologo(): ?InternalProfile
    {
        return $this->anestesiologo;
    }

    public function setAnestesiologo(?InternalProfile $anestesiologo): static
    {
        $this->anestesiologo = $anestesiologo;

        return $this;
    }

    public function getProcedimientoPropuesto(): ?string
    {
        return $this->procedimientoPropuesto;
    }

    public function setProcedimientoPropuesto(string $procedimientoPropuesto): static
    {
        $this->procedimientoPropuesto = $procedimientoPropuesto;

        return $this;
    }

    public function getDiagnosticoPreoperatorio(): ?string
    {
        return $this->diagnosticoPreoperatorio;
    }

    public function setDiagnosticoPreoperatorio(string $diagnosticoPreoperatorio): static
    {
        $this->diagnosticoPreoperatorio = $diagnosticoPreoperatorio;

        return $this;
    }

    public function getEstado(): ?CirugiaEstados
    {
        return $this->estado;
    }

    public function setEstado(CirugiaEstados $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getLateralidad(): ?string
    {
        return $this->lateralidad;
    }

    public function setLateralidad(?string $lateralidad): static
    {
        $this->lateralidad = $lateralidad;

        return $this;
    }

    public function getFechaHoraProgramada(): ?\DateTime
    {
        return $this->fechaHoraProgramada;
    }

    public function setFechaHoraProgramada(\DateTime $fechaHoraProgramada): static
    {
        $this->fechaHoraProgramada = $fechaHoraProgramada;

        return $this;
    }

    public function getHoraIngresoSala(): ?\DateTime
    {
        return $this->horaIngresoSala;
    }

    public function setHoraIngresoSala(?\DateTime $horaIngresoSala): static
    {
        $this->horaIngresoSala = $horaIngresoSala;

        return $this;
    }

    public function getHoraInicioAnestesia(): ?\DateTime
    {
        return $this->horaInicioAnestesia;
    }

    public function setHoraInicioAnestesia(?\DateTime $horaInicioAnestesia): static
    {
        $this->horaInicioAnestesia = $horaInicioAnestesia;

        return $this;
    }

    public function getHoraIncision(): ?\DateTime
    {
        return $this->horaIncision;
    }

    public function setHoraIncision(?\DateTime $horaIncision): static
    {
        $this->horaIncision = $horaIncision;

        return $this;
    }

    public function getHoraCierre(): ?\DateTime
    {
        return $this->horaCierre;
    }

    public function setHoraCierre(?\DateTime $horaCierre): static
    {
        $this->horaCierre = $horaCierre;

        return $this;
    }

    public function getHoraSalidaSala(): ?\DateTime
    {
        return $this->horaSalidaSala;
    }

    public function setHoraSalidaSala(?\DateTime $horaSalidaSala): static
    {
        $this->horaSalidaSala = $horaSalidaSala;

        return $this;
    }

    public function getCirugiaEstadosBadgeConfig(): array
    {
        switch ($this->getEstado()) {
            case CirugiaEstados::PROGRAMADA:
                return [
                    'class' => 'text-bg-secondary',
                    'label' => CirugiaEstados::PROGRAMADA->getReadableText()
                ];
            case CirugiaEstados::PRE_OP:
                return [
                    'class' => 'text-bg-warning',
                    'label' => CirugiaEstados::PRE_OP->getReadableText()
                ];
            case CirugiaEstados::TRANS_OP:
                return [
                    'class' => 'text-bg-danger',
                    'label' => CirugiaEstados::TRANS_OP->getReadableText()
                ];
            case CirugiaEstados::POST_OP:
                return [
                    'class' => 'text-bg-primary',
                    'label' => CirugiaEstados::POST_OP->getReadableText()
                ];
            case CirugiaEstados::FINALIZADA:
                return [
                    'class' => 'text-bg-success',
                    'label' => CirugiaEstados::FINALIZADA->getReadableText()
                ];
            case CirugiaEstados::CANCELADA:
                return [
                    'class' => 'text-bg-danger',
                    'label' => CirugiaEstados::CANCELADA->getReadableText()
                ];
        }

        return [
            'class' => 'text-bg-danger',
            'label' => 'Error'
        ];
    }

    public function getMotivoCancelacion(): ?string
    {
        return $this->motivoCancelacion;
    }

    public function setMotivoCancelacion(?string $motivoCancelacion): static
    {
        $this->motivoCancelacion = $motivoCancelacion;

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
            $audit->setCirugia($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): static
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getCirugia() === $this) {
                $audit->setCirugia(null);
            }
        }

        return $this;
    }
}
