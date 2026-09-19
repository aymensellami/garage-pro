<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $brand = null;

    #[ORM\Column(length: 100)]
    private ?string $model = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $registration = null;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $vin = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?int $mileage = null;

    #[ORM\Column(length: 20)]
    private ?string $fuelType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $engineCode = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $technicalControlDate = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $owner = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Intervention::class, orphanRemoval: true)]
    private Collection $interventions;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Appointment::class, orphanRemoval: true)]
    private Collection $appointments;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: MaintenanceAlert::class, orphanRemoval: true)]
    private Collection $maintenanceAlerts;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Quote::class)]
    private Collection $quotes;

    public function __construct()
    {
        $this->interventions = new ArrayCollection();
        $this->appointments = new ArrayCollection();
        $this->maintenanceAlerts = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): static
    {
        $this->registration = $registration;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): static
    {
        $this->vin = $vin;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function setFuelType(string $fuelType): static
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    public function getEngineCode(): ?string
    {
        return $this->engineCode;
    }

    public function setEngineCode(?string $engineCode): static
    {
        $this->engineCode = $engineCode;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getTechnicalControlDate(): ?\DateTimeInterface
    {
        return $this->technicalControlDate;
    }

    public function setTechnicalControlDate(?\DateTimeInterface $technicalControlDate): static
    {
        $this->technicalControlDate = $technicalControlDate;

        return $this;
    }

    public function getOwner(): ?Customer
    {
        return $this->owner;
    }

    public function setOwner(?Customer $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAge(): int
    {
        return (new \DateTime())->format('Y') - $this->year;
    }

    public function isTechnicalControlValid(): bool
    {
        if (null === $this->technicalControlDate) {
            return false;
        }
        $deadline = (clone $this->technicalControlDate)->modify('+2 years');

        return $deadline > new \DateTime();
    }

    public function getLastOilChange(): ?Intervention
    {
        $oilChanges = $this->interventions->filter(
            static fn (Intervention $i) => Intervention::STATUS_COMPLETED === $i->getStatus()
                && \in_array('Vidange', $i->getOperations() ?? [])
        );

        return $oilChanges->isEmpty() ? null : $oilChanges->last();
    }

    /** @return Collection<int, Intervention> */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    /** @return Collection<int, Appointment> */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    /** @return Collection<int, MaintenanceAlert> */
    public function getMaintenanceAlerts(): Collection
    {
        return $this->maintenanceAlerts;
    }

    /** @return Collection<int, Quote> */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }
}
