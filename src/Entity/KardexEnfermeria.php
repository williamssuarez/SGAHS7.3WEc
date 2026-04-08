<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\KardexEnfermeriaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KardexEnfermeriaRepository::class)]
class KardexEnfermeria
{
    use SoftDeletetableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'kardexEnfermerias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?IndicacionMedica $indicacionMedica = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndicacionMedica(): ?IndicacionMedica
    {
        return $this->indicacionMedica;
    }

    public function setIndicacionMedica(?IndicacionMedica $indicacionMedica): static
    {
        $this->indicacionMedica = $indicacionMedica;

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

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): static
    {
        $this->observacion = $observacion;

        return $this;
    }
}
