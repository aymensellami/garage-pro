<?php
namespace App\Entity;

use App\Repository\InvoiceLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceLineRepository::class)]
class InvoiceLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $tvaRate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column]
    private ?int $sortOrder = 0;

    public function getId(): ?int { return $this->id; }
    public function getInvoice(): ?Invoice { return $this->invoice; }
    public function setInvoice(?Invoice $invoice): static { $this->invoice = $invoice; return $this; }
    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }
    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
    public function getUnitPrice(): ?string { return $this->unitPrice; }
    public function setUnitPrice(string $unitPrice): static { $this->unitPrice = $unitPrice; return $this; }
    public function getTvaRate(): ?string { return $this->tvaRate; }
    public function setTvaRate(?string $tvaRate): static { $this->tvaRate = $tvaRate; return $this; }
    public function getDiscount(): ?string { return $this->discount; }
    public function setDiscount(?string $discount): static { $this->discount = $discount; return $this; }
    public function getSortOrder(): ?int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }

    public function getSubtotalHT(): float
    {
        $total = (float) $this->unitPrice * $this->quantity;
        if ($this->discount) {
            $total -= (float) $this->discount;
        }
        return max(0, $total);
    }

    public function getSubtotalTTC(): float
    {
        $rate = $this->tvaRate ? (float) $this->tvaRate : 20.0;
        return $this->getSubtotalHT() * (1 + $rate / 100);
    }
}
