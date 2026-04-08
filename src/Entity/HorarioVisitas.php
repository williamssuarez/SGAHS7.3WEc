<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Enum\CitasEstados;
use App\Repository\HorarioVisitasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HorarioVisitasRepository::class)]
class HorarioVisitas
{
    use SoftDeletetableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'horarioVisitas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Area $area = null;

    #[ORM\Column]
    private ?int $diaSemana = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaInicio = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaFin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getDiaSemana(): ?int
    {
        return $this->diaSemana;
    }

    public function setDiaSemana(int $diaSemana): static
    {
        $this->diaSemana = $diaSemana;

        return $this;
    }

    public function getHoraInicio(): ?\DateTime
    {
        return $this->horaInicio;
    }

    public function setHoraInicio(\DateTime $horaInicio): static
    {
        $this->horaInicio = $horaInicio;

        return $this;
    }

    public function getHoraFin(): ?\DateTime
    {
        return $this->horaFin;
    }

    public function setHoraFin(\DateTime $horaFin): static
    {
        $this->horaFin = $horaFin;

        return $this;
    }

    public function getReadableDias(): ?string
    {
        switch ($this->getDiaSemana()) {
            case 1:
                return 'Lunes';
            case 2:
                return 'Martes';
            case 3:
                return 'Miercoles';
            case 4:
                return 'Jueves';
            case 5:
                return 'Viernes';
            case 6:
                return 'Sábado';
            case 7:
                return 'Domingo';
        }
    }
}
