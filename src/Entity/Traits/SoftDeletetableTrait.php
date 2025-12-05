<?php

namespace App\Entity\Traits;

use App\Entity\StatusRecord;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait SoftDeletetableTrait
{
    #[ORM\Column]
    private ?int $uidCreate = null;

    #[ORM\Column]
    private ?int $uidUpdate = null;

    #[ORM\Column(type: 'datetime_immutable')] // Immutable is preferred for timestamps
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: 'datetime_immutable')] // Immutable is preferred for timestamps
    private ?\DateTimeInterface $updated = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?StatusRecord $status = null;

    public function getUidCreate(): ?int
    {
        return $this->uidCreate;
    }

    public function setUidCreate(int $uidCreate): static
    {
        $this->uidCreate = $uidCreate;

        return $this;
    }

    public function getUidUpdate(): ?int
    {
        return $this->uidUpdate;
    }

    public function setUidUpdate(int $uidUpdate): static
    {
        $this->uidUpdate = $uidUpdate;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): static
    {
        $this->updated = $updated;

        return $this;
    }

    public function getStatus(): ?StatusRecord
    {
        return $this->status;
    }

    public function setStatus(?StatusRecord $status): static
    {
        $this->status = $status;

        return $this;
    }
}
