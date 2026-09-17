<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Part;
use PHPUnit\Framework\TestCase;

class PartTest extends TestCase
{
    public function testStockStatusOk(): void
    {
        $part = new Part();
        $part->setStockQuantity(10);
        $part->setMinStockAlert(5);
        $this->assertEquals(Part::STATUS_OK, $part->getStockStatus());
        $this->assertFalse($part->isLowStock());
        $this->assertFalse($part->isOutOfStock());
    }

    public function testStockStatusLow(): void
    {
        $part = new Part();
        $part->setStockQuantity(3);
        $part->setMinStockAlert(5);
        $this->assertEquals(Part::STATUS_LOW, $part->getStockStatus());
        $this->assertTrue($part->isLowStock());
    }

    public function testStockStatusOut(): void
    {
        $part = new Part();
        $part->setStockQuantity(0);
        $part->setMinStockAlert(5);
        $this->assertEquals(Part::STATUS_OUT, $part->getStockStatus());
        $this->assertTrue($part->isOutOfStock());
    }

    public function testReserveDecreasesStock(): void
    {
        $part = new Part();
        $part->setStockQuantity(10);
        $this->assertTrue($part->reserve(3));
        $this->assertEquals(7, $part->getStockQuantity());
    }

    public function testReserveFailsWhenInsufficient(): void
    {
        $part = new Part();
        $part->setStockQuantity(2);
        $this->assertFalse($part->reserve(5));
    }

    public function testGetInventoryValue(): void
    {
        $part = new Part();
        $part->setStockQuantity(5);
        $part->setUnitPrice('12.50');
        $this->assertEquals(62.50, $part->getInventoryValue());
    }
}
