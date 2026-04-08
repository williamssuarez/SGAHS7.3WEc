<?php

namespace App\Entity;

use App\Entity\Traits\CoreVitalesTrait;
use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\SignosVitalesHospitalariosRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\LogEntry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SignosVitalesHospitalariosRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
class SignosVitalesHospitalarios
{
    use SoftDeletetableTrait;
    use CoreVitalesTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'signosVitalesHospitalarios')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hospitalizaciones $hospitalizacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHospitalizacion(): ?Hospitalizaciones
    {
        return $this->hospitalizacion;
    }

    public function setHospitalizacion(?Hospitalizaciones $hospitalizacion): static
    {
        $this->hospitalizacion = $hospitalizacion;

        return $this;
    }
}
