<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * @return Appointment[]
     */
    public function findForDateRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.scheduledAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('a.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}