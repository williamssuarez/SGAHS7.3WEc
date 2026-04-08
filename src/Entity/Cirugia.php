<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\CirugiaRepository;
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
    #[ORM\JoinColumn(nullable: false)]
    private ?Paciente $paciente = null;

    #[ORM\ManyToOne(inversedBy: 'cirugias')]
    private ?Quirofano $quirofano = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $medicoCirujano = null;

    #[ORM\ManyToOne]
    private ?User $anestesiologo = null;

    #[ORM\Column(length: 255)]
    private ?string $procedimientoPropuesto = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column]
    private ?\DateTime $fechaPrograma = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaCorte = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $horaCierre = null;

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

    public function getQuirofano(): ?Quirofano
    {
        return $this->quirofano;
    }

    public function setQuirofano(?Quirofano $quirofano): static
    {
        $this->quirofano = $quirofano;

        return $this;
    }

    public function getMedicoCirujano(): ?User
    {
        return $this->medicoCirujano;
    }

    public function setMedicoCirujano(?User $medicoCirujano): static
    {
        $this->medicoCirujano = $medicoCirujano;

        return $this;
    }

    public function getAnestesiologo(): ?User
    {
        return $this->anestesiologo;
    }

    public function setAnestesiologo(?User $anestesiologo): static
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

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFechaPrograma(): ?\DateTime
    {
        return $this->fechaPrograma;
    }

    public function setFechaPrograma(\DateTime $fechaPrograma): static
    {
        $this->fechaPrograma = $fechaPrograma;

        return $this;
    }

    public function getHoraCorte(): ?\DateTime
    {
        return $this->horaCorte;
    }

    public function setHoraCorte(?\DateTime $horaCorte): static
    {
        $this->horaCorte = $horaCorte;

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
}
