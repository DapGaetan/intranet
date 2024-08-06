<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $brand = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, VehicleReservation>
     */
    #[ORM\OneToMany(targetEntity: VehicleReservation::class, mappedBy: 'vehicle')]
    private Collection $vehicleReservations;

    public function __construct()
    {
        $this->vehicleReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
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

    /**
     * @return Collection<int, VehicleReservation>
     */
    public function getVehicleReservations(): Collection
    {
        return $this->vehicleReservations;
    }

    public function addVehicleReservation(VehicleReservation $vehicleReservation): static
    {
        if (!$this->vehicleReservations->contains($vehicleReservation)) {
            $this->vehicleReservations->add($vehicleReservation);
            $vehicleReservation->setVehicle($this);
        }

        return $this;
    }

    public function removeVehicleReservation(VehicleReservation $vehicleReservation): static
    {
        if ($this->vehicleReservations->removeElement($vehicleReservation)) {
            // set the owning side to null (unless already changed)
            if ($vehicleReservation->getVehicle() === $this) {
                $vehicleReservation->setVehicle(null);
            }
        }

        return $this;
    }
}
