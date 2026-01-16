<?php

namespace App\Entity;

use App\Entity\Traits\SoftDeletetableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'app_user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use SoftDeletetableTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_INTERNAL = 'ROLE_INTERNAL';
    public const ROLE_EXTERNAL = 'ROLE_EXTERNAL';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, HistoriaPaciente>
     */
    #[ORM\OneToMany(targetEntity: HistoriaPaciente::class, mappedBy: 'doctor')]
    private Collection $historiaPacientes;

    #[ORM\OneToOne(inversedBy: 'webUser', cascade: ['persist', 'remove'])]
    private ?InternalProfile $internalProfile = null;

    #[ORM\OneToOne(inversedBy: 'webUser', cascade: ['persist', 'remove'])]
    private ?ExternalProfile $externalProfile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarUrl = null;

    public function __construct()
    {
        $this->historiaPacientes = new ArrayCollection();
    }

    public function getActiveProfile(): ?object
    {
        // Check Internal First (since they might have both roles)
        if ($this->internalProfile !== null && in_array('ROLE_INTERNAL', $this->getRoles())) {
            return $this->internalProfile;
        }

        // Fallback to External
        return $this->externalProfile;
    }

    public function getDisplayName(): string
    {
        $profile = $this->getActiveProfile();

        // Assuming both InternalProfile and ExternalProfile have 'nombre' and 'apellido'
        if ($profile && method_exists($profile, 'getNombre')) {
            return ucfirst($profile->getNombre()) . ' ' . ucfirst($profile->getApellido());
        }

        // Fallback if profile is incomplete
        return $this->email;
    }

    public function getDisplayNameorNothing(): string
    {
        $profile = $this->getActiveProfile();

        // Assuming both InternalProfile and ExternalProfile have 'nombre' and 'apellido'
        if ($profile && method_exists($profile, 'getNombre')) {
            return ucfirst($profile->getNombre()) . ' ' . ucfirst($profile->getApellido());
        }

        // Fallback if profile is incomplete
        return 'Sin Datos registrados.';
    }

    public function getDisplayRoleLabel(): string
    {
        if (in_array('ROLE_INTERNAL', $this->getRoles())) {
            return 'Personal Médico'; // or 'Staff'
        }
        return 'Paciente';
    }

    public function getBadgeConfig(): array
    {
        // Check hierarchy manually or just check specific high-level roles
        if (in_array('ROLE_ADMIN', $this->getRoles()) || in_array('ROLE_INTERNAL', $this->getRoles())) {
            return [
                'class' => 'text-bg-primary', // Blue for Staff
                'label' => 'Personal Médico'
            ];
        }

        // Default fallback for patients/externals
        return [
            'class' => 'text-bg-success', // Green for Patients
            'label' => 'Paciente'
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, HistoriaPaciente>
     */
    public function getHistoriaPacientes(): Collection
    {
        return $this->historiaPacientes;
    }

    public function addHistoriaPaciente(HistoriaPaciente $historiaPaciente): static
    {
        if (!$this->historiaPacientes->contains($historiaPaciente)) {
            $this->historiaPacientes->add($historiaPaciente);
            $historiaPaciente->setDoctor($this);
        }

        return $this;
    }

    public function removeHistoriaPaciente(HistoriaPaciente $historiaPaciente): static
    {
        if ($this->historiaPacientes->removeElement($historiaPaciente)) {
            // set the owning side to null (unless already changed)
            if ($historiaPaciente->getDoctor() === $this) {
                $historiaPaciente->setDoctor(null);
            }
        }

        return $this;
    }

    public function getInternalProfile(): ?InternalProfile
    {
        return $this->internalProfile;
    }

    public function setInternalProfile(?InternalProfile $internalProfile): static
    {
        $this->internalProfile = $internalProfile;

        if ($internalProfile !== null && $internalProfile->getWebUser() !== $this) {
            $internalProfile->setWebUser($this);
        }

        return $this;
    }

    public function getExternalProfile(): ?ExternalProfile
    {
        return $this->externalProfile;
    }

    public function setExternalProfile(?ExternalProfile $externalProfile): static
    {
        $this->externalProfile = $externalProfile;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }
}
