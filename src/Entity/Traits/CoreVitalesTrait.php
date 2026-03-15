<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait CoreVitalesTrait
{
    #[ORM\Column(nullable: true)] // Made nullable for fast ER entry
    #[Assert\Range(notInRangeMessage: "Temperatura fuera de rango fisiológico (34°C - 42°C)", min: 34, max: 42)]
    #[Gedmo\Versioned]
    private ?float $temperatura = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Presion sistolica no es fisicamente posible (70 - 250).", min: 70, max: 250)]
    #[Gedmo\Versioned]
    private ?int $paSistolica = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Presion diastolica no es fisicamente posible (40 - 150).", min: 40, max: 150)]
    #[Gedmo\Versioned]
    private ?int $paDiastolica = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Frecuencia cardíaca no plausible (30 - 250 lpm)", min: 30, max: 250)]
    #[Gedmo\Versioned]
    private ?int $frecuenciaCardiaca = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "Frecuencia respiratoria no plausible (8 - 80 rpm)", min: 8, max: 80)]
    #[Gedmo\Versioned]
    private ?int $frecuenciaRespiratoria = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(notInRangeMessage: "El SpO2 debe estar entre 50% y 100%", min: 50, max: 100)]
    #[Gedmo\Versioned]
    private ?float $spo2 = null;

    public function getTemperatura(): ?float
    {
        return $this->temperatura;
    }

    public function setTemperatura(float $temperatura): static
    {
        $this->temperatura = $temperatura;

        return $this;
    }

    public function getPaSistolica(): ?int
    {
        return $this->paSistolica;
    }

    public function setPaSistolica(int $paSistolica): static
    {
        $this->paSistolica = $paSistolica;

        return $this;
    }

    public function getPaDiastolica(): ?int
    {
        return $this->paDiastolica;
    }

    public function setPaDiastolica(int $paDiastolica): static
    {
        $this->paDiastolica = $paDiastolica;

        return $this;
    }

    public function getFrecuenciaCardiaca(): ?int
    {
        return $this->frecuenciaCardiaca;
    }

    public function setFrecuenciaCardiaca(int $frecuenciaCardiaca): static
    {
        $this->frecuenciaCardiaca = $frecuenciaCardiaca;

        return $this;
    }

    public function getFrecuenciaRespiratoria(): ?int
    {
        return $this->frecuenciaRespiratoria;
    }

    public function setFrecuenciaRespiratoria(int $frecuenciaRespiratoria): static
    {
        $this->frecuenciaRespiratoria = $frecuenciaRespiratoria;

        return $this;
    }

    public function getSpo2(): ?float
    {
        return $this->spo2;
    }

    public function setSpo2(float $spo2): static
    {
        $this->spo2 = $spo2;

        return $this;
    }

    #[Assert\Callback]
    public function validateBloodPressure(ExecutionContextInterface $context, $payload): void
    {
        if ($this->paSistolica !== null && $this->paDiastolica !== null) {
            if ($this->paDiastolica >= $this->paSistolica) {
                $context->buildViolation('Presion diastolica no puede ser mas alta o igual a la presion sistolica.')
                    ->atPath('paDiastolica')
                    ->addViolation();
            }
        }
    }
}
