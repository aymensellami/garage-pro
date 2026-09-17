<?php
namespace App\Service;

use App\Entity\Part;
use App\Entity\StockMovement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class StockManagerService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function addStock(Part $part, int $quantity, string $reason, User $user): StockMovement
    {
        $part->setStockQuantity($part->getStockQuantity() + $quantity);

        $movement = new StockMovement();
        $movement->setPart($part);
        $movement->setType(StockMovement::TYPE_ENTRY);
        $movement->setQuantity($quantity);
        $movement->setReason($reason);
        $movement->setUser($user);

        $this->em->persist($movement);
        $this->em->flush();

        return $movement;
    }

    public function removeStock(Part $part, int $quantity, string $reason, User $user, ?\App\Entity\Intervention $intervention = null): bool
    {
        if ($part->getStockQuantity() < $quantity) {
            return false;
        }

        $part->setStockQuantity($part->getStockQuantity() - $quantity);

        $movement = new StockMovement();
        $movement->setPart($part);
        $movement->setType(StockMovement::TYPE_EXIT);
        $movement->setQuantity($quantity);
        $movement->setReason($reason);
        $movement->setUser($user);
        $movement->setIntervention($intervention);

        $this->em->persist($movement);
        $this->em->flush();

        return true;
    }
}
