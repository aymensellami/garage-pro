<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartRepository::class)]
class Part
{
    public const STATUS_OK = 'ok';
    public const STATUS_LOW = 'low_stock';
    public const STATUS_OUT = 'out_of_stock';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $stockQuantity = 0;

    #[ORM\Column]
    private ?int $minStockAlert = 5;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\ManyToOne(inversedBy: 'parts')]
    private ?Supplier $supplier = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column]
    private bool $isActive = true;

    /**
     * @var Collection<int, InterventionPart>
     */
    #[ORM\OneToMany(mappedBy: 'part', targetEntity: InterventionPart::class)]
    private Collection $interventionParts;

    /**
     * @var Collection<int, StockMovement>
     */
    #[ORM\OneToMany(mappedBy: 'part', targetEntity: StockMovement::class, orphanRemoval: true)]
    private Collection $stockMovements;

    public function __construct()
    {
        $this->interventionParts = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getMinStockAlert(): ?int
    {
        return $this->minStockAlert;
    }

    public function setMinStockAlert(int $minStockAlert): static
    {
        $this->minStockAlert = $minStockAlert;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getStockStatus(): string
    {
        if ($this->stockQuantity <= 0) {
            return self::STATUS_OUT;
        }
        if ($this->stockQuantity <= $this->minStockAlert) {
            return self::STATUS_LOW;
        }

        return self::STATUS_OK;
    }

    public function isLowStock(): bool
    {
        return self::STATUS_LOW === $this->getStockStatus();
    }

    public function isOutOfStock(): bool
    {
        return self::STATUS_OUT === $this->getStockStatus();
    }

    public function getInventoryValue(): float
    {
        return (float) $this->unitPrice * (int) $this->stockQuantity;
    }

    public function reserve(int $qty): bool
    {
        if ($this->stockQuantity < $qty) {
            return false;
        }
        $this->stockQuantity -= $qty;

        return true;
    }

    public function release(int $qty): static
    {
        $this->stockQuantity += $qty;

        return $this;
    }

    /** @return Collection<int, InterventionPart> */
    public function getInterventionParts(): Collection
    {
        return $this->interventionParts;
    }

    /** @return Collection<int, StockMovement> */
    public function getStockMovements(): Collection
    {
        return $this->stockMovements;
    }
}
