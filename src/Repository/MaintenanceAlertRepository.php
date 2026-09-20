<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaintenanceAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MaintenanceAlert>
 */
class MaintenanceAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceAlert::class);
    }
}