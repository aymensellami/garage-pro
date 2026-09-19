<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Intervention;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\StockMovement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InterventionWorkflowService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function complete(Intervention $intervention, User $user): void
    {
        if (Intervention::STATUS_IN_PROGRESS !== $intervention->getStatus()) {
            throw new \RuntimeException('L\'intervention doit être en cours pour être terminée.');
        }

        $intervention->markAsCompleted();

        // Consommation stock
        foreach ($intervention->getInterventionParts() as $ip) {
            $part = $ip->getPart();
            $part->reserve($ip->getQuantity());

            $movement = new StockMovement();
            $movement->setPart($part);
            $movement->setType(StockMovement::TYPE_EXIT);
            $movement->setQuantity($ip->getQuantity());
            $movement->setReason('Consommation intervention '.$intervention->getReference());
            $movement->setUser($user);
            $movement->setIntervention($intervention);
            $this->em->persist($movement);
        }

        // Génération facture
        $invoice = new Invoice();
        $invoice->setIntervention($intervention);
        $invoice->setCustomer($intervention->getVehicle()->getOwner());
        $invoice->setTvaRate('20.00');

        // Ligne main d'œuvre
        $laborLine = new InvoiceLine();
        $laborLine->setLabel('Main d\'œuvre — '.$intervention->getDescription());
        $laborLine->setQuantity(1);
        $laborLine->setUnitPrice($intervention->getFinalCost() ?? $intervention->getEstimatedCost());
        $laborLine->setTvaRate('20.00');
        $invoice->addLine($laborLine);

        // Lignes pièces
        foreach ($intervention->getInterventionParts() as $ip) {
            $line = new InvoiceLine();
            $line->setLabel($ip->getPart()->getName());
            $line->setQuantity($ip->getQuantity());
            $line->setUnitPrice($ip->getUnitPriceAtTime());
            $line->setTvaRate('20.00');
            $invoice->addLine($line);
        }

        $invoice->calculateTotals();
        $this->em->persist($invoice);

        $intervention->markAsInvoiced();
    }
}
