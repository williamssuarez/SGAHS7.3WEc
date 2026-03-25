<?php

namespace App\Entity;

use App\Repository\MainConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MainConfigurationRepository::class)]
class MainConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombreInstitucion = null;

    #[ORM\Column(length: 255)]
    private ?string $abreviaturaInstitucion = null;

    #[ORM\Column(length: 255)]
    private ?string $logo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreInstitucion(): ?string
    {
        return $this->nombreInstitucion;
    }

    public function setNombreInstitucion(string $nombreInstitucion): static
    {
        $this->nombreInstitucion = $nombreInstitucion;

        return $this;
    }

    public function getAbreviaturaInstitucion(): ?string
    {
        return $this->abreviaturaInstitucion;
    }

    public function setAbreviaturaInstitucion(string $abreviaturaInstitucion): static
    {
        $this->abreviaturaInstitucion = $abreviaturaInstitucion;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }
}
