<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Entity\Traits\CoreVitalesTrait;
use App\Repository\VitalesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: VitalesRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class Vitales
{
    use SoftDeletetableTrait;
    use CoreVitalesTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive(message: "El peso debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "Peso excede el límite permitido", max: 700)]
    #[Gedmo\Versioned]
    private ?float $peso = null;

    #[ORM\Column]
    #[Assert\Positive(message: "La altura debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "Altura fuera de rango (10cm - 250cm)", min: 10, max: 280)]
    #[Gedmo\Versioned]
    private ?float $altura = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "El CMB debe ser mayor a 0")]
    #[Assert\Range(notInRangeMessage: "CMB no plausible", min: 5, max: 60)]
    #[Gedmo\Versioned]
    private ?float $cmb = null;

    #[ORM\Column]
    #[Gedmo\Versioned]
    private ?float $imc = null;

    #[ORM\ManyToOne(inversedBy: 'vitales')]
    private ?Consulta $consulta = null;

    public function getId(): ?int
    {
        return $this->id;
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
