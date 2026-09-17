<?php
namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Invoice
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_SENT = 'sent';
    public const STATUS_PAID = 'paid';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $number = null;

    #[ORM\OneToOne(inversedBy: 'invoice', targetEntity: Intervention::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalHT = '0.00';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private ?string $tvaRate = '20.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalTTC = '0.00';

    #[ORM\Column(length: 30)]
    private ?string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $issuedAt = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceLine::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $lines;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Payment::class, orphanRemoval: true)]
    private Collection $payments;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->issuedAt = new \DateTime();
        $this->dueDate = (new \DateTime())->modify('+30 days');
    }

    #[ORM\PrePersist]
    public function generateNumber(): void
    {
        if ($this->number === null) {
            $this->number = 'FAC-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getNumber(): ?string { return $this->number; }
    public function getIntervention(): ?Intervention { return $this->intervention; }
    public function setIntervention(Intervention $intervention): static { $this->intervention = $intervention; return $this; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): static { $this->customer = $customer; return $this; }
    public function getTotalHT(): ?string { return $this->totalHT; }
    public function setTotalHT(string $totalHT): static { $this->totalHT = $totalHT; return $this; }
    public function getTvaRate(): ?string { return $this->tvaRate; }
    public function setTvaRate(string $tvaRate): static { $this->tvaRate = $tvaRate; return $this; }
    public function getTotalTTC(): ?string { return $this->totalTTC; }
    public function setTotalTTC(string $totalTTC): static { $this->totalTTC = $totalTTC; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getIssuedAt(): ?\DateTimeInterface { return $this->issuedAt; }
    public function getDueDate(): ?\DateTimeInterface { return $this->dueDate; }
    public function setDueDate(\DateTimeInterface $dueDate): static { $this->dueDate = $dueDate; return $this; }
    public function getPaidAt(): ?\DateTimeInterface { return $this->paidAt; }
    public function setPaidAt(?\DateTimeInterface $paidAt): static { $this->paidAt = $paidAt; return $this; }
    public function getPaymentMethod(): ?string { return $this->paymentMethod; }
    public function setPaymentMethod(?string $paymentMethod): static { $this->paymentMethod = $paymentMethod; return $this; }

    public function getLines(): Collection { return $this->lines; }
    public function addLine(InvoiceLine $line): static
    {
        if (!$this->lines->contains($line)) {
            $this->lines->add($line);
            $line->setInvoice($this);
        }
        return $this;
    }

    public function getPayments(): Collection { return $this->payments; }

    public function calculateTotals(): static
    {
        $ht = array_sum($this->lines->map(fn(InvoiceLine $l) => $l->getSubtotalHT())->toArray());
        $this->totalHT = number_format($ht, 2, '.', '');
        $this->totalTTC = number_format($ht * (1 + (float) $this->tvaRate / 100), 2, '.', '');
        return $this;
    }

    public function getTotalPaid(): float
    {
        return array_sum($this->payments->map(fn(Payment $p) => (float) $p->getAmount())->toArray());
    }

    public function getRemainingAmount(): float
    {
        return (float) $this->totalTTC - $this->getTotalPaid();
    }

    public function markAsPaid(): static
    {
        $this->status = self::STATUS_PAID;
        $this->paidAt = new \DateTime();
        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID && $this->dueDate < new \DateTime();
    }
}
