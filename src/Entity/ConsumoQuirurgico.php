<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ConsumoQuirurgicoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ConsumoQuirurgicoRepository::class)]
class ConsumoQuirurgico
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consumoQuirurgicos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cirugia $cirugia = null;

    #[ORM\ManyToOne(inversedBy: 'consumoQuirurgicos')]
    private ?Articulo $articuloInventario = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaHora = null;

    #[ORM\Column]
    private ?int $cantidad = null;

    #[ORM\Column]
    private ?bool $aportadoPorPaciente = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcionArticuloExterno = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    // The attribute tells Symfony: "Run this method during validation"
    #[Assert\Callback]
    public function validateHybridOrigin(ExecutionContextInterface $context, $payload): void
    {
        // SCENARIO 1: The patient brought it
        if ($this->isAportadoPorPaciente()) {

            // Check 1: Did the nurse forget to type what the patient brought?
            if (empty($this->getDescripcionArticuloExterno())) {
                $context->buildViolation('Debe describir el artículo o kit que trajo el paciente.')
                    ->atPath('descripcionArticuloExterno') // Attaches the error to THIS specific field
                    ->addViolation();
            }

            // Check 2: Did the nurse accidentally leave the hospital dropdown selected?
            if ($this->getArticuloInventario() !== null) {
                $context->buildViolation('Si el paciente aportó el insumo, no debe seleccionar un artículo del inventario.')
                    ->atPath('articuloInventario') // Attaches the error to the dropdown
                    ->addViolation();
            }
        }
        // SCENARIO 2: The hospital provided it
        else {
            // Check 3: Did the nurse forget to select an item from the dropdown?
            if ($this->getArticuloInventario() === null) {
                $context->buildViolation('Si es un insumo del hospital, debe seleccionar el artículo del inventario.')
                    ->atPath('articuloInventario')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCirugia(): ?Cirugia
    {
        return $this->cirugia;
    }

    public function setCirugia(?Cirugia $cirugia): static
    {
        $this->cirugia = $cirugia;

        return $this;
    }

    public function getArticuloInventario(): ?Articulo
    {
        return $this->articuloInventario;
    }

    public function setArticuloInventario(?Articulo $articuloInventario): static
    {
        $this->articuloInventario = $articuloInventario;

        return $this;
    }

    public function getFechaHora(): ?\DateTime
    {
        return $this->fechaHora;
    }

    public function setFechaHora(?\DateTime $fechaHora): static
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getCantidad(): ?int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function isAportadoPorPaciente(): ?bool
    {
        return $this->aportadoPorPaciente;
    }

    public function setAportadoPorPaciente(bool $aportadoPorPaciente): static
    {
        $this->aportadoPorPaciente = $aportadoPorPaciente;

        return $this;
    }

    public function getDescripcionArticuloExterno(): ?string
    {
        return $this->descripcionArticuloExterno;
    }

    public function setDescripcionArticuloExterno(?string $descripcionArticuloExterno): static
    {
        $this->descripcionArticuloExterno = $descripcionArticuloExterno;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }
}
