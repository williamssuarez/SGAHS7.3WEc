<?php

namespace App\Entity;

use App\Repository\AttachmentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttachmentsRepository::class)]
class Attachments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $filetype = null;

    #[ORM\Column(length: 255)]
    private ?string $filehash = null;

    #[ORM\Column]
    private ?\DateTime $dateUploaded = null;

    #[ORM\ManyToOne(inversedBy: 'attachments')]
    private ?Paciente $paciente = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFiletype(): ?string
    {
        return $this->filetype;
    }

    public function setFiletype(string $filetype): static
    {
        $this->filetype = $filetype;

        return $this;
    }

    public function getFilehash(): ?string
    {
        return $this->filehash;
    }

    public function setFilehash(string $filehash): static
    {
        $this->filehash = $filehash;

        return $this;
    }

    public function getDateUploaded(): ?\DateTime
    {
        return $this->dateUploaded;
    }

    public function setDateUploaded(\DateTime $dateUploaded): static
    {
        $this->dateUploaded = $dateUploaded;

        return $this;
    }

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }
}
