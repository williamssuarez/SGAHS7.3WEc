<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\TurnoDoctorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\LogEntry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TurnoDoctorRepository::class)]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
#[Assert\Callback(callback: 'validateDates')]
#[ORM\HasLifecycleCallbacks]
class TurnoDoctor
{
    use SoftDeletetableTrait;

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->startTime !== null && $this->endTime !== null) {
            if ($this->endTime < $this->startTime) {
                $context->buildViolation('La hora de finalización no puede ser anterior al inicio.')
                    ->atPath('fechaFin')
                    ->addViolation();
            }
        }
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'turnoDoctores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InternalProfile $doctor = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $slotDuration = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $endTime = null;

    #[ORM\ManyToOne(inversedBy: 'turnoDoctores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Especialidades $especialidad = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctor(): ?InternalProfile
    {
        return $this->doctor;
    }

    public function setDoctor(?InternalProfile $doctor): static
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getSlotDuration(): ?int
    {
        return $this->slotDuration;
    }

    public function setSlotDuration(int $slotDuration): static
    {
        $this->slotDuration = $slotDuration;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getEspecialidad(): ?Especialidades
    {
        return $this->especialidad;
    }

    public function setEspecialidad(?Especialidades $especialidad): static
    {
        $this->especialidad = $especialidad;

        return $this;
    }
}
