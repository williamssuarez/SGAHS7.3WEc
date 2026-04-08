<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ProtocoloQuirurgicoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocoloQuirurgicoRepository::class)]
class ProtocoloQuirurgico
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cirugia $cirugia = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnosticoPreoperatorio = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnosticoPostoperatorio = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hallazgos = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tecnicaQuirurgica = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $complicaciones = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sangradoEstimado = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCirugia(): ?Cirugia
    {
        return $this->cirugia;
    }

    public function setCirugia(Cirugia $cirugia): static
    {
        $this->cirugia = $cirugia;

        return $this;
    }

    public function getDiagnosticoPreoperatorio(): ?string
    {
        return $this->diagnosticoPreoperatorio;
    }

    public function setDiagnosticoPreoperatorio(string $diagnosticoPreoperatorio): static
    {
        $this->diagnosticoPreoperatorio = $diagnosticoPreoperatorio;

        return $this;
    }

    public function getDiagnosticoPostoperatorio(): ?string
    {
        return $this->diagnosticoPostoperatorio;
    }

    public function setDiagnosticoPostoperatorio(string $diagnosticoPostoperatorio): static
    {
        $this->diagnosticoPostoperatorio = $diagnosticoPostoperatorio;

        return $this;
    }

    public function getHallazgos(): ?string
    {
        return $this->hallazgos;
    }

    public function setHallazgos(string $hallazgos): static
    {
        $this->hallazgos = $hallazgos;

        return $this;
    }

    public function getTecnicaQuirurgica(): ?string
    {
        return $this->tecnicaQuirurgica;
    }

    public function setTecnicaQuirurgica(string $tecnicaQuirurgica): static
    {
        $this->tecnicaQuirurgica = $tecnicaQuirurgica;

        return $this;
    }

    public function getComplicaciones(): ?string
    {
        return $this->complicaciones;
    }

    public function setComplicaciones(?string $complicaciones): static
    {
        $this->complicaciones = $complicaciones;

        return $this;
    }

    public function getSangradoEstimado(): ?string
    {
        return $this->sangradoEstimado;
    }

    public function setSangradoEstimado(?string $sangradoEstimado): static
    {
        $this->sangradoEstimado = $sangradoEstimado;

        return $this;
    }
}
