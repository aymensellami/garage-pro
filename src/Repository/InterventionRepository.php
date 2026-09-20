<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function countThisMonth(): int
    {
        $start = new \DateTime('first day of this month');
        $end = new \DateTime('last day of this month 23:59:59');

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Intervention[]
     */
    public function findPendingForDate(\DateTimeInterface $date): array
    {
        $start = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0);
        $end = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('i')
            ->where('i.scheduledAt BETWEEN :start AND :end')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('statuses', [Intervention::STATUS_PENDING, Intervention::STATUS_IN_PROGRESS])
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyRevenue(): float
    {
        $start = new \DateTime('first day of this month');
        $end = new \DateTime('last day of this month 23:59:59');

        $result = $this->createQueryBuilder('i')
            ->select('SUM(i.finalCost)')
            ->where('i.status = :status')
            ->andWhere('i.completedAt BETWEEN :start AND :end')
            ->setParameter('status', Intervention::STATUS_INVOICED)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * @return array<string, int>
     */
    public function getWeeklyActivity(): array
    {
        $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $activity = [];
        $today = new \DateTime();

        for ($i = 6; $i >= 0; --$i) {
            $date = clone $today;
            $date->modify("-$i days");
            $start = clone $date;
            $start->setTime(0, 0);
            $end = clone $date;
            $end->setTime(23, 59, 59);

            $count = $this->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->where('i.scheduledAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();

            $dayName = $days[$date->format('N') - 1];
            $activity[$dayName] = (int) $count;
        }

        return $activity;
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $results = $this->createQueryBuilder('i')
            ->select('i.status, COUNT(i.id) as count')
            ->groupBy('i.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = $row['count'];
        }

        return $counts;
    }
}