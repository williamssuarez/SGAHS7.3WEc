<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\HospitalizacionCondicionAlta;
use App\Enum\HospitalizacionEstados;
use App\Repository\HospitalizacionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HospitalizacionesRepository::class)]
class Hospitalizaciones
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hospitalizaciones')]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'hospitalizaciones')]
    private ?HospitalizacionCama $camaActual = null;

    #[ORM\ManyToOne(inversedBy: 'hospitalizaciones')]
    private ?User $medicoTratante = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Emergencia $emergencia = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Consulta $consulta = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaIngreso = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnosticoIngreso = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaEgreso = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $diagnosticoEgreso = null;

    #[ORM\Column(enumType: HospitalizacionEstados::class)]
    private ?HospitalizacionEstados $estado = null;

    #[ORM\Column(nullable: true, enumType: HospitalizacionCondicionAlta::class)]
    private ?HospitalizacionCondicionAlta $condicionAlta = null;

    /**
     * @var Collection<int, EvolucionHospitalaria>
     */
    #[ORM\OneToMany(targetEntity: EvolucionHospitalaria::class, mappedBy: 'hospitalizacion')]
    private Collection $evolucionHospitalarias;

    /**
     * @var Collection<int, IndicacionMedica>
     */
    #[ORM\OneToMany(targetEntity: IndicacionMedica::class, mappedBy: 'hospitalizacion')]
    private Collection $indicacionMedicas;

    /**
     * @var Collection<int, SignosVitalesHospitalarios>
     */
    #[ORM\OneToMany(targetEntity: SignosVitalesHospitalarios::class, mappedBy: 'hospitalizacion')]
    private Collection $signosVitalesHospitalarios;

    #[ORM\Column(nullable: true)]
    private ?bool $visitasPermitidas = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notaRestriccionVisitas = null;

    /**
     * @var Collection<int, VisitaHospitalaria>
     */
    #[ORM\OneToMany(targetEntity: VisitaHospitalaria::class, mappedBy: 'hospitalizacion')]
    private Collection $visitaHospitalarias;

    /**
     * @var Collection<int, Cirugia>
     */
    #[ORM\OneToMany(targetEntity: Cirugia::class, mappedBy: 'hospitalizacionOrigen')]
    private Collection $cirugias;

    /**
     * @var Collection<int, Audit>
     */
    #[ORM\OneToMany(targetEntity: Audit::class, mappedBy: 'hospitalizacion')]
    private Collection $audits;

    public function __construct()
    {
        $this->evolucionHospitalarias = new ArrayCollection();
        $this->indicacionMedicas = new ArrayCollection();
        $this->signosVitalesHospitalarios = new ArrayCollection();
        $this->visitaHospitalarias = new ArrayCollection();
        $this->cirugias = new ArrayCollection();
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

    public function getCamaActual(): ?HospitalizacionCama
    {
        return $this->camaActual;
    }

    public function setCamaActual(?HospitalizacionCama $camaActual): static
    {
        $this->camaActual = $camaActual;

        return $this;
    }

    public function getMedicoTratante(): ?User
    {
        return $this->medicoTratante;
    }

    public function setMedicoTratante(?User $medicoTratante): static
    {
        $this->medicoTratante = $medicoTratante;

        return $this;
    }

    public function getEmergencia(): ?Emergencia
    {
        return $this->emergencia;
    }

    public function setEmergencia(?Emergencia $emergencia): static
    {
        $this->emergencia = $emergencia;

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

    public function getFechaIngreso(): ?\DateTime
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?\DateTime $fechaIngreso): static
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getDiagnosticoIngreso(): ?string
    {
        return $this->diagnosticoIngreso;
    }

    public function setDiagnosticoIngreso(string $diagnosticoIngreso): static
    {
        $this->diagnosticoIngreso = $diagnosticoIngreso;

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

    public function getDiagnosticoEgreso(): ?string
    {
        return $this->diagnosticoEgreso;
    }

    public function setDiagnosticoEgreso(?string $diagnosticoEgreso): static
    {
        $this->diagnosticoEgreso = $diagnosticoEgreso;

        return $this;
    }

    public function getEstado(): ?HospitalizacionEstados
    {
        return $this->estado;
    }

    public function setEstado(HospitalizacionEstados $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getCondicionAlta(): ?HospitalizacionCondicionAlta
    {
        return $this->condicionAlta;
    }

    public function setCondicionAlta(?HospitalizacionCondicionAlta $condicionAlta): static
    {
        $this->condicionAlta = $condicionAlta;

        return $this;
    }

    /**
     * @return Collection<int, EvolucionHospitalaria>
     */
    public function getEvolucionHospitalarias(): Collection
    {
        return $this->evolucionHospitalarias;
    }

    public function addEvolucionHospitalaria(EvolucionHospitalaria $evolucionHospitalaria): static
    {
        if (!$this->evolucionHospitalarias->contains($evolucionHospitalaria)) {
            $this->evolucionHospitalarias->add($evolucionHospitalaria);
            $evolucionHospitalaria->setHospitalizacion($this);
        }

        return $this;
    }

    public function removeEvolucionHospitalaria(EvolucionHospitalaria $evolucionHospitalaria): static
    {
        if ($this->evolucionHospitalarias->removeElement($evolucionHospitalaria)) {
            // set the owning side to null (unless already changed)
            if ($evolucionHospitalaria->getHospitalizacion() === $this) {
                $evolucionHospitalaria->setHospitalizacion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IndicacionMedica>
     */
    public function getIndicacionMedicas(): Collection
    {
        return $this->indicacionMedicas;
    }

    public function addIndicacionMedica(IndicacionMedica $indicacionMedica): static
    {
        if (!$this->indicacionMedicas->contains($indicacionMedica)) {
            $this->indicacionMedicas->add($indicacionMedica);
            $indicacionMedica->setHospitalizacion($this);
        }

        return $this;
    }

    public function removeIndicacionMedica(IndicacionMedica $indicacionMedica): static
    {
        if ($this->indicacionMedicas->removeElement($indicacionMedica)) {
            // set the owning side to null (unless already changed)
            if ($indicacionMedica->getHospitalizacion() === $this) {
                $indicacionMedica->setHospitalizacion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SignosVitalesHospitalarios>
     */
    public function getSignosVitalesHospitalarios(): Collection
    {
        return $this->signosVitalesHospitalarios;
    }

    public function addSignosVitalesHospitalario(SignosVitalesHospitalarios $signosVitalesHospitalario): static
    {
        if (!$this->signosVitalesHospitalarios->contains($signosVitalesHospitalario)) {
            $this->signosVitalesHospitalarios->add($signosVitalesHospitalario);
            $signosVitalesHospitalario->setHospitalizacion($this);
        }

        return $this;
    }

    public function removeSignosVitalesHospitalario(SignosVitalesHospitalarios $signosVitalesHospitalario): static
    {
        if ($this->signosVitalesHospitalarios->removeElement($signosVitalesHospitalario)) {
            // set the owning side to null (unless already changed)
            if ($signosVitalesHospitalario->getHospitalizacion() === $this) {
                $signosVitalesHospitalario->setHospitalizacion(null);
            }
        }

        return $this;
    }

    public function isVisitasPermitidas(): ?bool
    {
        return $this->visitasPermitidas;
    }

    public function setVisitasPermitidas(?bool $visitasPermitidas): static
    {
        $this->visitasPermitidas = $visitasPermitidas;

        return $this;
    }

    public function getNotaRestriccionVisitas(): ?string
    {
        return $this->notaRestriccionVisitas;
    }

    public function setNotaRestriccionVisitas(?string $notaRestriccionVisitas): static
    {
        $this->notaRestriccionVisitas = $notaRestriccionVisitas;

        return $this;
    }

    /**
     * @return Collection<int, VisitaHospitalaria>
     */
    public function getVisitaHospitalarias(): Collection
    {
        return $this->visitaHospitalarias;
    }

    public function addVisitaHospitalaria(VisitaHospitalaria $visitaHospitalaria): static
    {
        if (!$this->visitaHospitalarias->contains($visitaHospitalaria)) {
            $this->visitaHospitalarias->add($visitaHospitalaria);
            $visitaHospitalaria->setHospitalizacion($this);
        }

        return $this;
    }

    public function removeVisitaHospitalaria(VisitaHospitalaria $visitaHospitalaria): static
    {
        if ($this->visitaHospitalarias->removeElement($visitaHospitalaria)) {
            // set the owning side to null (unless already changed)
            if ($visitaHospitalaria->getHospitalizacion() === $this) {
                $visitaHospitalaria->setHospitalizacion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cirugia>
     */
    public function getCirugias(): Collection
    {
        return $this->cirugias;
    }

    public function addCirugia(Cirugia $cirugia): static
    {
        if (!$this->cirugias->contains($cirugia)) {
            $this->cirugias->add($cirugia);
            $cirugia->setHospitalizacionOrigen($this);
        }

        return $this;
    }

    public function removeCirugia(Cirugia $cirugia): static
    {
        if ($this->cirugias->removeElement($cirugia)) {
            // set the owning side to null (unless already changed)
            if ($cirugia->getHospitalizacionOrigen() === $this) {
                $cirugia->setHospitalizacionOrigen(null);
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
            $audit->setHospitalizacion($this);
        }

        return $this;
    }

    public function removeAudit(Audit $audit): static
    {
        if ($this->audits->removeElement($audit)) {
            // set the owning side to null (unless already changed)
            if ($audit->getHospitalizacion() === $this) {
                $audit->setHospitalizacion(null);
            }
        }

        return $this;
    }
}
