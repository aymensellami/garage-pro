<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InterventionPartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterventionPartRepository::class)]
class InterventionPart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'interventionParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\ManyToOne(inversedBy: 'interventionParts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Part $part = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPriceAtTime = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $discountPercent = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPart(): ?Part
    {
        return $this->part;
    }

    public function setPart(?Part $part): static
    {
        $this->part = $part;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPriceAtTime(): ?string
    {
        return $this->unitPriceAtTime;
    }

    public function setUnitPriceAtTime(string $unitPriceAtTime): static
    {
        $this->unitPriceAtTime = $unitPriceAtTime;

        return $this;
    }

    public function getDiscountPercent(): ?string
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(?string $discountPercent): static
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    public function getSubtotal(): float
    {
        return (float) $this->unitPriceAtTime * $this->quantity;
    }

    public function getTotal(): float
    {
        $subtotal = $this->getSubtotal();
        if ($this->discountPercent) {
            $subtotal *= (1 - (float) $this->discountPercent / 100);
        }

        return \round($subtotal, 2);
    }
}
