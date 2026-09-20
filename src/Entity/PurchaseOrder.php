<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PurchaseOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseOrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PurchaseOrder
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 30)]
    private ?string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalAmount = '0.00';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $orderedAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $expectedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $receivedAt = null;

    /**
     * @var Collection<int, PurchaseOrderLine>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: PurchaseOrderLine::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $lines;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->orderedAt = new \DateTime();
    }

    #[ORM\PrePersist]
    public function generateReference(): void
    {
        if (null === $this->reference) {
            $this->reference = 'BC-'.\date('Y').'-'.\str_pad((string) \random_int(1, 99999), 5, '0', \STR_PAD_LEFT);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
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

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getOrderedAt(): ?\DateTimeInterface
    {
        return $this->orderedAt;
    }

    public function getExpectedAt(): ?\DateTimeInterface
    {
        return $this->expectedAt;
    }

    public function setExpectedAt(?\DateTimeInterface $expectedAt): static
    {
        $this->expectedAt = $expectedAt;

        return $this;
    }

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeInterface $receivedAt): static
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    /**
     * @return Collection<int, PurchaseOrderLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(PurchaseOrderLine $line): static
    {
        if (!$this->lines->contains($line)) {
            $this->lines->add($line);
            $line->setOrder($this);
        }

        return $this;
    }

    public function markAsReceived(): static
    {
        $this->status = self::STATUS_RECEIVED;
        $this->receivedAt = new \DateTime();

        return $this;
    }
}