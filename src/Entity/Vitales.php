<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\VitalesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: VitalesRepository::class)]
#[Assert\Callback(callback: 'validateBloodPressure')]
#[ORM\HasLifecycleCallbacks] // Vital: Tells Doctrine to watch for events
class Vitales
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Temperatura fuera de rango fisiológico (34°C - 42°C)", min: 34, max: 42)]
    private ?float $temperatura = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Presion sistolica no es fisicamente posible (70 - 250).", min: 70, max: 250)]
    private ?int $paSistolica = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Presion diastolica no es fisicamente posible (40 - 150).", min: 40, max: 150)]
    private ?int $paDiastolica = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Frecuencia cardíaca no plausible (30 - 250 lpm)", min: 30, max: 250)]
    private ?int $frecuenciaCardiaca = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "Frecuencia respiratoria no plausible (8 - 80 rpm)", min: 8, max: 80)]
    private ?int $frecuenciaRespiratoria = null;

    #[ORM\Column]
    #[Assert\Range(notInRangeMessage: "El SpO2 debe estar entre 50% y 100%", min: 50, max: 100)]
    private ?float $spo2 = null;

    #[ORM\Column]
    #[Assert\Positive(message: "El peso debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "Peso excede el límite permitido", max: 700)]
    private ?float $peso = null;

    #[ORM\Column]
    #[Assert\Positive(message: "La altura debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "Altura fuera de rango (10cm - 250cm)", min: 10, max: 280)]
    private ?float $altura = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "El CMB debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "CMB no plausible", min: 5, max: 60)]
    private ?float $cmb = null;

    #[ORM\Column]
    private ?float $imc = null;

    #[ORM\ManyToOne(inversedBy: 'vitales')]
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

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculateImcValue(): void
    {
        if ($this->peso > 0 && $this->altura > 0) {
            $altMetros = $this->altura / 100;
            $this->imc = round($this->peso / ($altMetros ** 2), 2);
        } else {
            $this->imc = null;
        }
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

    public function getStatusColor(string $type): string
    {
        return match($type) {
            'temp' => ($this->temperatura >= 38.0 || $this->temperatura < 36.0) ? 'table-danger' : '',
            'fc'   => ($this->frecuenciaCardiaca > 100 || $this->frecuenciaCardiaca < 60) ? 'table-warning' : '',
            'fr'   => ($this->frecuenciaRespiratoria > 20 || $this->frecuenciaRespiratoria < 12) ? 'table-warning' : '',
            'spo2' => ($this->spo2 < 94) ? 'table-danger' : '',
            'imc'  => ($this->imc >= 30 || $this->imc < 18.5) ? 'table-danger' : ($this->imc >= 25 ? 'table-warning' : ''),
            default => '',
        };
    }

    public function getStatusColorCard(string $type): string
    {
        return match($type) {
            'temp' => ($this->temperatura >= 38.0 || $this->temperatura < 36.0) ? 'danger' : 'secondary',
            'fc'   => ($this->frecuenciaCardiaca > 100 || $this->frecuenciaCardiaca < 60) ? 'warning' : 'secondary',
            'fr'   => ($this->frecuenciaRespiratoria > 20 || $this->frecuenciaRespiratoria < 12) ? 'warning' : 'secondary',
            'spo2' => ($this->spo2 < 94) ? 'danger' : 'secondary',
            'imc'  => ($this->imc >= 30 || $this->imc < 18.5) ? 'table-danger' : ($this->imc >= 25 ? 'warning' : 'secondary'),
            default => 'secondary',
        };
    }
}
