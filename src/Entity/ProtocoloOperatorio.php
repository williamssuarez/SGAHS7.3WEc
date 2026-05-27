<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\ProtocoloOperatorioRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProtocoloOperatorioRepository::class)]
class ProtocoloOperatorio
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'protocoloOperatorio', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cirugia $cirugia = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hallazgos = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tecnicaQuirurgica = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $complicaciones = null;

    #[ORM\Column]
    private ?int $sangradoEstimado = null;

    #[ORM\Column]
    private ?bool $envioPatologia = null;

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

    public function getSangradoEstimado(): ?int
    {
        return $this->sangradoEstimado;
    }

    public function setSangradoEstimado(int $sangradoEstimado): static
    {
        $this->sangradoEstimado = $sangradoEstimado;

        return $this;
    }

    public function isEnvioPatologia(): ?bool
    {
        return $this->envioPatologia;
    }

    public function setEnvioPatologia(bool $envioPatologia): static
    {
        $this->envioPatologia = $envioPatologia;

        return $this;
    }
}
