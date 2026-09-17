<?php

namespace App\Service;

use App\Repository\CustomerRepository;
use App\Repository\InterventionRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PartRepository;
use App\Repository\UserRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminStatsService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CustomerRepository $customerRepo,
        private InterventionRepository $interventionRepo,
        private InvoiceRepository $invoiceRepo,
        private PartRepository $partRepo,
        private UserRepository $userRepo,
        private VehicleRepository $vehicleRepo,
    ) {
    }

    /**
     * Statistiques globales de l'administration.
     */
    public function getGlobalStats(): array
    {
        return [
            'totalUsers' => $this->userRepo->count([]),
            'totalCustomers' => $this->customerRepo->count([]),
            'totalVehicles' => $this->vehicleRepo->count([]),
            'totalInterventions' => $this->interventionRepo->count([]),
            'totalInvoices' => $this->invoiceRepo->count([]),
            'totalParts' => $this->partRepo->count([]),
            'totalRevenue' => $this->invoiceRepo->getTotalRevenue(),
            'activeMechanics' => count(
                $this->userRepo->findByRole('ROLE_MECHANIC')
            ),
        ];
    }

    /**
     * Chiffre d'affaires par mois.
     */
    public function getRevenueByMonth(int $months = 12): array
    {
        $months = max(1, (int) $months);

        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                DATE_FORMAT(i.issued_at, '%Y-%m') AS month,
                SUM(i.total_ttc) AS revenue
            FROM invoice i
            WHERE i.status = 'paid'
              AND i.issued_at >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)
            GROUP BY month
            ORDER BY month
        ";

        return $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    /**
     * Nombre d'interventions par mois.
     */
    public function getInterventionsByMonth(int $months = 12): array
    {
        $months = max(1, (int) $months);

        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                DATE_FORMAT(scheduled_at, '%Y-%m') AS month,
                COUNT(*) AS count
            FROM intervention
            WHERE scheduled_at >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)
            GROUP BY month
            ORDER BY month
        ";

        return $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    /**
     * Top clients selon le chiffre d'affaires généré.
     */
    public function getTopCustomers(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                c.id,
                c.first_name,
                c.last_name,
                SUM(i.total_ttc) AS total
            FROM customer c
            JOIN invoice i ON i.customer_id = c.id
            WHERE i.status = 'paid'
            GROUP BY
                c.id,
                c.first_name,
                c.last_name
            ORDER BY total DESC
        ";

        $results = $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();

        return array_slice($results, 0, $limit);
    }

    /**
     * Top mécaniciens selon le nombre d'interventions et le revenu.
     */
    public function getTopMechanics(int $limit = 10): array
    {
        $limit = max(1, (int) $limit);

        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                COUNT(i.id) AS count,
                SUM(i.final_cost) AS revenue
            FROM user u
            JOIN intervention i ON i.mechanic_id = u.id
            WHERE i.status IN ('completed', 'invoiced')
            GROUP BY
                u.id,
                u.first_name,
                u.last_name
            ORDER BY count DESC
        ";

        $results = $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();

        return array_slice($results, 0, $limit);
    }

    /**
     * Pièces actuellement en stock.
     */
    public function getPartsByCategory(): array
    {
        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                p.name,
                p.stock_quantity,
                p.unit_price,
                (p.stock_quantity * p.unit_price) AS value
            FROM part p
            WHERE p.is_active = 1
            ORDER BY value DESC
            LIMIT 20
        ";

        return $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    /**
     * Acquisition des nouveaux clients par mois.
     */
    public function getCustomerAcquisition(int $months = 12): array
    {
        $months = max(1, (int) $months);

        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                COUNT(*) AS count
            FROM customer
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)
            GROUP BY month
            ORDER BY month
        ";

        return $conn
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    /**
     * Valeur totale du stock.
     */
    public function getStockValue(): float
    {
        $conn = $this->em->getConnection();

        $sql = "
            SELECT
                SUM(stock_quantity * unit_price) AS value
            FROM part
            WHERE is_active = 1
        ";

        $result = $conn
            ->executeQuery($sql)
            ->fetchOne();

        return (float) ($result ?? 0);
    }

    /**
     * Montant moyen des factures payées.
     */
    public function getAverageInvoiceAmount(): float
    {
        $conn = $this->em->getConnection();

        $sql = "
            SELECT AVG(total_ttc)
            FROM invoice
            WHERE status = 'paid'
        ";

        $result = $conn
            ->executeQuery($sql)
            ->fetchOne();

        return (float) ($result ?? 0);
    }
}

