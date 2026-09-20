<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PurchaseOrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PurchaseOrderLine>
 */
class PurchaseOrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseOrderLine::class);
    }
}
