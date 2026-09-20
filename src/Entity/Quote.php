<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Quote
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CONVERTED = 'converted';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $number = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    #[ORM\Column(type: 'json')]
    private array $operations = [];

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $parts = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalHT = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalTTC = '0.00';

    #[ORM\Column(length: 30)]
    private ?string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $validUntil = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'quote', targetEntity: Intervention::class)]
    private ?Intervention $intervention = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->validUntil = (new \DateTime())->modify('+30 days');
    }

    #[ORM\PrePersist]
    public function generateNumber(): void
    {
        if (null === $this->number) {
            $this->number = 'DEV-'.\date('Y').'-'.\str_pad((string) \random_int(1, 99999), 5, '0', \STR_PAD_LEFT);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

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
     * @return array<int, array<string, mixed>>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param array<int, array<string, mixed>> $operations
     */
    public function setOperations(array $operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getParts(): ?array
    {
        return $this->parts;
    }

    /**
     * @param array<int, array<string, mixed>>|null $parts
     */
    public function setParts(?array $parts): static
    {
        $this->parts = $parts;

        return $this;
    }

    public function getTotalHT(): ?string
    {
        return $this->totalHT;
    }

    public function setTotalHT(string $totalHT): static
    {
        $this->totalHT = $totalHT;

        return $this;
    }

    public function getTotalTTC(): ?string
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(string $totalTTC): static
    {
        $this->totalTTC = $totalTTC;

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

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(\DateTimeInterface $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function calculateTotals(): static
    {
        return $this;
    }

    public function convertToIntervention(): Intervention
    {
        $intervention = new Intervention();
        $intervention->setVehicle($this->vehicle);
        $intervention->setDescription($this->description);
        $intervention->setEstimatedCost($this->totalHT);
        $intervention->setStatus(Intervention::STATUS_PENDING);
        $this->status = self::STATUS_CONVERTED;
        $this->intervention = $intervention;

        return $intervention;
    }

    public function markAsAccepted(): static
    {
        $this->status = self::STATUS_ACCEPTED;

        return $this;
    }

    public function markAsRejected(): static
    {
        $this->status = self::STATUS_REJECTED;

        return $this;
    }

    public function isExpired(): bool
    {
        if (null === $this->validUntil) {
            return false;
        }

        return $this->validUntil < new \DateTime() && self::STATUS_SENT === $this->status;
    }
}
