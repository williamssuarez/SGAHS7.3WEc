<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\EmergenciasEstados;
use App\Repository\EmergenciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmergenciaRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    /**
     * @var Collection<int, EvolucionEmergencia>
     */
    #[ORM\OneToMany(targetEntity: EvolucionEmergencia::class, mappedBy: 'emergencia')]
    private Collection $evolucionEmergencias;

    #[ORM\OneToOne(mappedBy: 'emergencia', cascade: ['persist', 'remove'])]
    private ?AltaMedica $altaMedica = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->fechaIngreso = new \DateTime();
        $this->evolucionEmergencias = new ArrayCollection();
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

    #[Assert\Callback]
    public function validateIdentity(ExecutionContextInterface $context, $payload): void
    {
        $hasPaciente = $this->paciente !== null;
        $hasTemporal = !empty(trim((string)$this->pacienteTemporal));

        // Rule 1: Cannot have both empty
        if (!$hasPaciente && !$hasTemporal) {
            $context->buildViolation('Debe buscar un paciente registrado o ingresar un nombre temporal (Ej: "Hombre Desconocido 1").')
                ->atPath('pacienteTemporal')
                ->addViolation();
        }
    }

    /**
     * @return Collection<int, EvolucionEmergencia>
     */
    public function getEvolucionEmergencias(): Collection
    {
        return $this->evolucionEmergencias;
    }

    public function addEvolucionEmergencia(EvolucionEmergencia $evolucionEmergencia): static
    {
        if (!$this->evolucionEmergencias->contains($evolucionEmergencia)) {
            $this->evolucionEmergencias->add($evolucionEmergencia);
            $evolucionEmergencia->setEmergencia($this);
        }

        return $this;
    }

    public function removeEvolucionEmergencia(EvolucionEmergencia $evolucionEmergencia): static
    {
        if ($this->evolucionEmergencias->removeElement($evolucionEmergencia)) {
            // set the owning side to null (unless already changed)
            if ($evolucionEmergencia->getEmergencia() === $this) {
                $evolucionEmergencia->setEmergencia(null);
            }
        }

        return $this;
    }

    public function getAltaMedica(): ?AltaMedica
    {
        return $this->altaMedica;
    }

    public function setAltaMedica(?AltaMedica $altaMedica): static
    {
        // unset the owning side of the relation if necessary
        if ($altaMedica === null && $this->altaMedica !== null) {
            $this->altaMedica->setEmergencia(null);
        }

        // set the owning side of the relation if necessary
        if ($altaMedica !== null && $altaMedica->getEmergencia() !== $this) {
            $altaMedica->setEmergencia($this);
        }

        $this->altaMedica = $altaMedica;

        return $this;
    }
}
