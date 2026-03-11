<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\CitasConfiguracionesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CitasConfiguracionesRepository::class)]
#[Assert\Callback(callback: 'validateEdadPrioridad')]
#[Assert\Callback(callback: 'validateCapacity')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['especialidad'], message: 'Esta especialidad ya tiene una configuración activa.')]
class CitasConfiguraciones
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Especialidades $especialidad = null;

    /**
     * @var Collection<int, Consultorios>
     */
    #[ORM\ManyToMany(targetEntity: Consultorios::class, inversedBy: 'citasConfiguraciones')]
    private Collection $consultorio;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaInicio = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaFin = null;

    #[ORM\Column]
    private ?int $maxPacientesDia = null;

    #[ORM\Column]
    private ?bool $tieneEdadPrioridad = null;

    #[ORM\Column(nullable: true)]
    private ?int $edadPrioridad = null;

    #[ORM\Column]
    private ?int $duracionCita = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $diasSemana = [];

    #[ORM\Column]
    private ?bool $tieneTiempoReceso = null;

    #[ORM\Column]
    private ?int $tiempoReceso = null;

    public function __construct()
    {
        $this->consultorio = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Consultorios>
     */
    public function getConsultorio(): Collection
    {
        return $this->consultorio;
    }

    public function addConsultorio(Consultorios $consultorio): static
    {
        if (!$this->consultorio->contains($consultorio)) {
            $this->consultorio->add($consultorio);
        }

        return $this;
    }

    public function removeConsultorio(Consultorios $consultorio): static
    {
        $this->consultorio->removeElement($consultorio);

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

    public function getMaxPacientesDia(): ?int
    {
        return $this->maxPacientesDia;
    }

    public function setMaxPacientesDia(int $maxPacientesDia): static
    {
        $this->maxPacientesDia = $maxPacientesDia;

        return $this;
    }

    public function isTieneEdadPrioridad(): ?bool
    {
        return $this->tieneEdadPrioridad;
    }

    public function setTieneEdadPrioridad(bool $tieneEdadPrioridad): static
    {
        $this->tieneEdadPrioridad = $tieneEdadPrioridad;

        return $this;
    }

    public function getEdadPrioridad(): ?int
    {
        return $this->edadPrioridad;
    }

    public function setEdadPrioridad(?int $edadPrioridad): static
    {
        $this->edadPrioridad = $edadPrioridad;

        return $this;
    }

    public function getDuracionCita(): ?int
    {
        return $this->duracionCita;
    }

    public function setDuracionCita(int $duracionCita): static
    {
        $this->duracionCita = $duracionCita;

        return $this;
    }

    #[Assert\Callback]
    public function validateEdadPrioridad(ExecutionContextInterface $context, $payload): void
    {
        if ($this->tieneEdadPrioridad and $this->edadPrioridad === null){
            $context->buildViolation('Debe especificar una edad de prioridad.')
                ->atPath('edadPrioridad')
                ->addViolation();
        }
    }

    #[Assert\Callback]
    public function validateCapacity(ExecutionContextInterface $context, $payload): void
    {
        if (!$this->horaInicio || !$this->horaFin || !$this->duracionCita || $this->consultorio->isEmpty()) {
            return;
        }

        // 1. Total minutes available
        $interval = $this->horaInicio->diff($this->horaFin);
        $totalMinutes = ($interval->h * 60) + $interval->i;

        // 2. Determine actual "Block" size per patient
        // If they don't have a break, we treat it as 0
        $receso = ($this->isTieneTiempoReceso() && $this->getTiempoReceso())
            ? $this->getTiempoReceso()
            : 0;

        $minutosPorPaciente = $this->duracionCita + $receso;

        if ($minutosPorPaciente <= 0) return;

        // 3. Calculate Capacity
        $slotsPerOffice = floor($totalMinutes / $minutosPorPaciente);
        $totalCapacity = $slotsPerOffice * count($this->consultorio);

        // 4. Validation
        if ($this->maxPacientesDia > $totalCapacity) {
            $context->buildViolation('Capacidad insuficiente. Incluyendo el receso, cada cita ocupa {{ block }} minutos. El límite real para esta configuración es de {{ limit }} pacientes.')
                ->setParameter('{{ block }}', (string)$minutosPorPaciente)
                ->setParameter('{{ limit }}', (string)$totalCapacity)
                ->atPath('maxPacientesDia')
                ->addViolation();
        }
    }

    public function getDiasSemana(): array
    {
        return $this->diasSemana;
    }

    public function setDiasSemana(array $diasSemana): static
    {
        $this->diasSemana = $diasSemana;

        return $this;
    }

    public function isTieneTiempoReceso(): ?bool
    {
        return $this->tieneTiempoReceso;
    }

    public function setTieneTiempoReceso(bool $tieneTiempoReceso): static
    {
        $this->tieneTiempoReceso = $tieneTiempoReceso;

        return $this;
    }

    public function getTiempoReceso(): ?int
    {
        return $this->tiempoReceso;
    }

    public function setTiempoReceso(int $tiempoReceso): static
    {
        $this->tiempoReceso = $tiempoReceso;

        return $this;
    }
}
