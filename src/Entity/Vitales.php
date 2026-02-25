<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\VitalesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: VitalesRepository::class)]
#[Assert\Callback(callback: 'validateBloodPressure')]
class Vitales
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $temperatura = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Presion sistolica no es fisicamente posible.", min: 70, max: 250)]
    private ?int $paSistolica = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Presion diastolica no es fisicamente posible.", min: 40, max: 150)]
    private ?int $paDiastolica = null;

    #[ORM\Column]
    private ?int $frecuenciaCardiaca = null;

    #[ORM\Column]
    private ?int $frecuenciaRespiratoria = null;

    #[ORM\Column]
    private ?float $spo2 = null;

    #[ORM\Column]
    private ?float $peso = null;

    #[ORM\Column]
    private ?float $altura = null;

    #[ORM\Column(nullable: true)]
    private ?float $cmb = null;

    #[ORM\Column]
    private ?float $imc = null;

    #[ORM\OneToOne(mappedBy: 'vitales', cascade: ['persist', 'remove'])]
    private ?Consulta $consulta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getPeso(): ?float
    {
        return $this->peso;
    }

    public function setPeso(float $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getAltura(): ?float
    {
        return $this->altura;
    }

    public function setAltura(float $altura): static
    {
        $this->altura = $altura;

        return $this;
    }

    public function getCmb(): ?float
    {
        return $this->cmb;
    }

    public function setCmb(?float $cmb): static
    {
        $this->cmb = $cmb;

        return $this;
    }

    public function getImc(): ?float
    {
        return $this->imc;
    }

    public function setImc(float $imc): static
    {
        $this->imc = $imc;

        return $this;
    }

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

    public function getConsulta(): ?Consulta
    {
        return $this->consulta;
    }

    public function setConsulta(?Consulta $consulta): static
    {
        // unset the owning side of the relation if necessary
        if ($consulta === null && $this->consulta !== null) {
            $this->consulta->setVitales(null);
        }

        // set the owning side of the relation if necessary
        if ($consulta !== null && $consulta->getVitales() !== $this) {
            $consulta->setVitales($this);
        }

        $this->consulta = $consulta;

        return $this;
    }
}
