<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, length: 5, nullable: true)]
    private ?string $postalCode = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'department')]
    private Collection $users;

    /**
     * @var Collection<int, UserProfile>
     */
    #[ORM\OneToMany(targetEntity: UserProfile::class, mappedBy: 'department')]
    private Collection $userProfiles;

    /**
     * @var Collection<int, CulturalEventTicket>
     */
    #[ORM\OneToMany(targetEntity: CulturalEventTicket::class, mappedBy: 'department')]
    private Collection $culturalEventTickets;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->userProfiles = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
        $this->culturalEventTickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(): static
    {
        $this->name = implode(' ', array_filter([$this->address, $this->city, $this->postalCode]));
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        $this->setName(); // Update name whenever address is set
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        $this->setName(); // Update name whenever city is set
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        $this->setName(); // Update name whenever postalCode is set
        return $this;
    }


    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setDepartment($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserProfile>
     */
    public function getUserProfiles(): Collection
    {
        return $this->userProfiles;
    }

    public function addUserProfile(UserProfile $userProfile): static
    {
        if (!$this->userProfiles->contains($userProfile)) {
            $this->userProfiles->add($userProfile);
            $userProfile->setDepartment($this);
        }

        return $this;
    }

    public function removeUserProfile(UserProfile $userProfile): static
    {
        if ($this->userProfiles->removeElement($userProfile)) {
            // set the owning side to null (unless already changed)
            if ($userProfile->getDepartment() === $this) {
                $userProfile->setDepartment(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, CulturalEventTicket>
     */
    public function getCulturalEventTickets(): Collection
    {
        return $this->culturalEventTickets;
    }

    public function addCulturalEventTicket(CulturalEventTicket $culturalEventTicket): static
    {
        if (!$this->culturalEventTickets->contains($culturalEventTicket)) {
            $this->culturalEventTickets->add($culturalEventTicket);
            $culturalEventTicket->setDepartment($this);
        }

        return $this;
    }

    public function removeCulturalEventTicket(CulturalEventTicket $culturalEventTicket): static
    {
        if ($this->culturalEventTickets->removeElement($culturalEventTicket)) {
            // set the owning side to null (unless already changed)
            if ($culturalEventTicket->getDepartment() === $this) {
                $culturalEventTicket->setDepartment(null);
            }
        }

        return $this;
    }
}
