<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\VisitaHospitalariaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitaHospitalariaRepository::class)]
class VisitaHospitalaria
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'visitaHospitalarias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hospitalizaciones $hospitalizacion = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreVisitante = null;

    #[ORM\Column(length: 255)]
    private ?string $parentesco = null;

    #[ORM\Column]
    private ?\DateTime $fechaHoraEntrada = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaHoraSalida = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHospitalizacion(): ?Hospitalizaciones
    {
        return $this->hospitalizacion;
    }

    public function setHospitalizacion(?Hospitalizaciones $hospitalizacion): static
    {
        $this->hospitalizacion = $hospitalizacion;

        return $this;
    }

    public function getNombreVisitante(): ?string
    {
        return $this->nombreVisitante;
    }

    public function setNombreVisitante(string $nombreVisitante): static
    {
        $this->nombreVisitante = $nombreVisitante;

        return $this;
    }

    public function getParentesco(): ?string
    {
        return $this->parentesco;
    }

    public function setParentesco(string $parentesco): static
    {
        $this->parentesco = $parentesco;

        return $this;
    }

    public function getFechaHoraEntrada(): ?\DateTime
    {
        return $this->fechaHoraEntrada;
    }

    public function setFechaHoraEntrada(\DateTime $fechaHoraEntrada): static
    {
        $this->fechaHoraEntrada = $fechaHoraEntrada;

        return $this;
    }

    public function getFechaHoraSalida(): ?\DateTime
    {
        return $this->fechaHoraSalida;
    }

    public function setFechaHoraSalida(?\DateTime $fechaHoraSalida): static
    {
        $this->fechaHoraSalida = $fechaHoraSalida;

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
}
