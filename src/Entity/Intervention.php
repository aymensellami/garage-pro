<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Intervention
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WAITING_PARTS = 'waiting_parts';
    public const STATUS_QUALITY_CHECK = 'quality_check';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $reference = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    private ?User $mechanic = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $operations = [];

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $estimatedCost = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $finalCost = null;

    #[ORM\Column(length: 30)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $scheduledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $completedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var Collection<int, InterventionPart>
     */
    #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: InterventionPart::class, orphanRemoval: true)]
    private Collection $interventionParts;

    #[ORM\OneToOne(mappedBy: 'intervention', targetEntity: Invoice::class)]
    private ?Invoice $invoice = null;

    #[ORM\OneToOne(mappedBy: 'intervention', targetEntity: Quote::class)]
    private ?Quote $quote = null;

    public function __construct()
    {
        $this->interventionParts = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    #[ORM\PrePersist]
    public function generateReference(): void
    {
        if (null === $this->reference) {
            $this->reference = 'FI-'.\date('Y').'-'.\str_pad((string) \random_int(1, 9999), 4, '0', \STR_PAD_LEFT);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getMechanic(): ?User
    {
        return $this->mechanic;
    }

    public function setMechanic(?User $mechanic): static
    {
        $this->mechanic = $mechanic;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param string[] $operations
     */
    public function setOperations(array $operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    public function getEstimatedCost(): ?string
    {
        return $this->estimatedCost;
    }

    public function setEstimatedCost(string $estimatedCost): static
    {
        $this->estimatedCost = $estimatedCost;

        return $this;
    }

    public function getFinalCost(): ?string
    {
        return $this->finalCost;
    }

    public function setFinalCost(?string $finalCost): static
    {
        $this->finalCost = $finalCost;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeInterface $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /** @return Collection<int, InterventionPart> */
    public function getInterventionParts(): Collection
    {
        return $this->interventionParts;
    }

    public function addInterventionPart(InterventionPart $interventionPart): static
    {
        if (!$this->interventionParts->contains($interventionPart)) {
            $this->interventionParts->add($interventionPart);
            $interventionPart->setIntervention($this);
        }

        return $this;
    }

    public function getTotalPartsCost(): float
    {
        return \array_sum($this->interventionParts->map(static fn (InterventionPart $ip) => $ip->getTotal())->toArray());
    }

    public function getProfitMargin(): ?float
    {
        if (null === $this->finalCost) {
            return null;
        }
        $partsCost = $this->getTotalPartsCost();
        $labor = (float) $this->finalCost - $partsCost;

        return $labor > 0 ? \round(($labor / (float) $this->finalCost) * 100, 2) : 0;
    }

    public function markAsInProgress(): static
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->startedAt = new \DateTime();

        return $this;
    }

    public function markAsCompleted(): static
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = new \DateTime();
        if ($this->startedAt) {
            $this->durationMinutes = (int) (($this->completedAt->getTimestamp() - $this->startedAt->getTimestamp()) / 60);
        }

        return $this;
    }

    public function markAsInvoiced(): static
    {
        $this->status = self::STATUS_INVOICED;

        return $this;
    }

    public function canBeEdited(): bool
    {
        return \in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true);
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }
}