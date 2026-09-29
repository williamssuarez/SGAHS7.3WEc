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
#[ORM\HasLifecycleCallbacks]
#[\App\Validator\OneActiveConfigPerSpecialty]
class CitasConfiguraciones
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Especialidades::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Especialidades $especialidad = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isActive = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private ?int $maxPacientesDia = null;

    #[ORM\Column]
    private ?bool $tieneEdadPrioridad = null;

    #[ORM\Column(nullable: true)]
    private ?int $edadPrioridad = null;

    #[ORM\Column]
    private ?int $duracionCita = null;

    #[ORM\Column]
    private ?bool $tieneTiempoReceso = null;

    #[ORM\Column(nullable: true)]
    private ?int $tiempoReceso = null;

    public function __construct()
    {
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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

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

    public function setTiempoReceso(?int $tiempoReceso): static
    {
        $this->tiempoReceso = $tiempoReceso;

        return $this;
    }
}
