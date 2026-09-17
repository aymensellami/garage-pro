<?php
namespace App\Entity;

use App\Repository\PurchaseOrderLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseOrderLineRepository::class)]
class PurchaseOrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PurchaseOrder $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Part $part = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column]
    private ?int $receivedQty = 0;

    public function getId(): ?int { return $this->id; }
    public function getOrder(): ?PurchaseOrder { return $this->order; }
    public function setOrder(?PurchaseOrder $order): static { $this->order = $order; return $this; }
    public function getPart(): ?Part { return $this->part; }
    public function setPart(?Part $part): static { $this->part = $part; return $this; }
    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
    public function getUnitPrice(): ?string { return $this->unitPrice; }
    public function setUnitPrice(string $unitPrice): static { $this->unitPrice = $unitPrice; return $this; }
    public function getReceivedQty(): ?int { return $this->receivedQty; }
    public function setReceivedQty(int $receivedQty): static { $this->receivedQty = $receivedQty; return $this; }

    public function getRemainingQty(): int
    {
        return $this->quantity - $this->receivedQty;
    }
}
