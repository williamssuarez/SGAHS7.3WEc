<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\HospitalizacionCondicionGeneral;
use App\Repository\EvolucionHospitalariaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EvolucionHospitalariaRepository::class)]
class EvolucionHospitalaria
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evolucionHospitalarias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hospitalizaciones $hospitalizacion = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $subjetivo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $objetivo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $analisis = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $plan = null;

    #[ORM\Column(enumType: HospitalizacionCondicionGeneral::class)]
    private ?HospitalizacionCondicionGeneral $condicionGeneral = null;

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

    public function getSubjetivo(): ?string
    {
        return $this->subjetivo;
    }

    public function setSubjetivo(string $subjetivo): static
    {
        $this->subjetivo = $subjetivo;

        return $this;
    }

    public function getObjetivo(): ?string
    {
        return $this->objetivo;
    }

    public function setObjetivo(string $objetivo): static
    {
        $this->objetivo = $objetivo;

        return $this;
    }

    public function getAnalisis(): ?string
    {
        return $this->analisis;
    }

    public function setAnalisis(string $analisis): static
    {
        $this->analisis = $analisis;

        return $this;
    }

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getCondicionGeneral(): ?HospitalizacionCondicionGeneral
    {
        return $this->condicionGeneral;
    }

    public function setCondicionGeneral(HospitalizacionCondicionGeneral $condicionGeneral): static
    {
        $this->condicionGeneral = $condicionGeneral;

        return $this;
    }
}
