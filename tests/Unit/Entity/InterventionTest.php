<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Intervention;
use PHPUnit\Framework\TestCase;

class InterventionTest extends TestCase
{
    public function testReferenceIsGeneratedOnPersist(): void
    {
        $intervention = new Intervention();
        $intervention->setDescription('Test intervention');
        $intervention->setEstimatedCost('100.00');
        $intervention->setScheduledAt(new \DateTime());

        // Simuler PrePersist
        $intervention->generateReference();

        $this->assertNotNull($intervention->getReference());
        $this->assertStringStartsWith('FI-', $intervention->getReference());
    }

    public function testCanBeEditedReturnsTrueForPending(): void
    {
        $intervention = new Intervention();
        $this->assertTrue($intervention->canBeEdited());
    }

    public function testCanBeEditedReturnsFalseForCompleted(): void
    {
        $intervention = new Intervention();
        $intervention->markAsCompleted();
        $this->assertFalse($intervention->canBeEdited());
    }

    public function testMarkAsInProgressSetsStartedAt(): void
    {
        $intervention = new Intervention();
        $intervention->markAsInProgress();
        $this->assertNotNull($intervention->getStartedAt());
        $this->assertEquals(Intervention::STATUS_IN_PROGRESS, $intervention->getStatus());
    }
}
