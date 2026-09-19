<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MaintenanceAlertRepository;
use App\Repository\VehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app_dashboard', methods: ['GET'])]
    public function index(
        CustomerRepository $customerRepository,
        VehicleRepository $vehicleRepository,
        AppointmentRepository $appointmentRepository,
        InvoiceRepository $invoiceRepository,
        MaintenanceAlertRepository $maintenanceAlertRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * Client correspondant au compte connecté.
         * Ici la liaison se fait par l'adresse email.
         */
        $customer = $customerRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
        ]);

        /*
         * Aucun client associé au compte.
         */
        if (!$customer) {
            return $this->render('dashboard/index.html.twig', [
                'customer' => null,
                'vehicles' => [],
                'appointments' => [],
                'invoices' => [],
                'alerts' => [],
                'stats' => [
                    'totalVehicles' => 0,
                    'upcomingAppointments' => 0,
                    'totalInvoices' => 0,
                    'unresolvedAlerts' => 0,
                ],
            ]);
        }

        /*
         * =========================================================
         * MES VÉHICULES
         * =========================================================
         */
        $vehicles = $vehicleRepository->findBy(
            ['owner' => $customer],
            ['createdAt' => 'DESC']
        );

        /*
         * =========================================================
         * MES PROCHAINS RENDEZ-VOUS
         * =========================================================
         */
        $appointments = $appointmentRepository
            ->createQueryBuilder('a')
            ->join('a.vehicle', 'v')
            ->where('v.owner = :customer')
            ->andWhere('a.scheduledAt >= :now')
            ->andWhere('a.status NOT IN (:cancelled)')
            ->setParameter('customer', $customer)
            ->setParameter('now', new \DateTime())
            ->setParameter('cancelled', ['cancelled'])
            ->orderBy('a.scheduledAt', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        /*
         * =========================================================
         * MES DERNIÈRES FACTURES
         * =========================================================
         */
        $invoices = $invoiceRepository
            ->createQueryBuilder('i')
            ->where('i.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('i.issuedAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        /*
         * =========================================================
         * MES ALERTES DE MAINTENANCE
         * =========================================================
         */
        $alerts = $maintenanceAlertRepository
            ->createQueryBuilder('a')
            ->join('a.vehicle', 'v')
            ->where('v.owner = :customer')
            ->andWhere('a.isResolved = :resolved')
            ->setParameter('customer', $customer)
            ->setParameter('resolved', false)
            ->orderBy('a.triggeredAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        /*
         * =========================================================
         * DASHBOARD
         * =========================================================
         */
        return $this->render('dashboard/index.html.twig', [
            'customer' => $customer,

            'vehicles' => $vehicles,

            'appointments' => $appointments,

            'invoices' => $invoices,

            'alerts' => $alerts,

            'stats' => [
                'totalVehicles' => \count($vehicles),
                'upcomingAppointments' => \count($appointments),
                'totalInvoices' => \count($invoices),
                'unresolvedAlerts' => \count($alerts),
            ],
        ]);
    }
}
