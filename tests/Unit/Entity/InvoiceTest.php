<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    public function testCalculateTotals(): void
    {
        $invoice = new Invoice();
        $invoice->setTvaRate('20.00');

        $line1 = new InvoiceLine();
        $line1->setLabel('Main d\'œuvre');
        $line1->setQuantity(1);
        $line1->setUnitPrice('100.00');
        $invoice->addLine($line1);

        $line2 = new InvoiceLine();
        $line2->setLabel('Pièce');
        $line2->setQuantity(2);
        $line2->setUnitPrice('50.00');
        $invoice->addLine($line2);

        $invoice->calculateTotals();

        $this->assertEquals('200.00', $invoice->getTotalHT());
        $this->assertEquals('240.00', $invoice->getTotalTTC());
    }

    public function testIsOverdue(): void
    {
        $invoice = new Invoice();
        $invoice->setDueDate((new \DateTime())->modify('-1 day'));
        $invoice->setStatus(Invoice::STATUS_ISSUED);
        $this->assertTrue($invoice->isOverdue());
    }

    public function testMarkAsPaid(): void
    {
        $invoice = new Invoice();
        $invoice->markAsPaid();
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->getStatus());
        $this->assertNotNull($invoice->getPaidAt());
    }
}
