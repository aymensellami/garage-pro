<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\MaintenanceAlertRepository;
use App\Repository\PartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mechanic')]
#[IsGranted('ROLE_MECHANIC')]
class MechanicController extends AbstractController
{
    #[Route('/dashboard', name: 'app_mechanic_dashboard', methods: ['GET'])]
    public function dashboard(
        InterventionRepository $interventionRepo,
        MaintenanceAlertRepository $alertRepo,
        PartRepository $partRepo
    ): Response {
        $today = new \DateTime();

        $pendingInterventions = $interventionRepo->findPendingForDate($today);

        $alerts = $alertRepo->findBy(
            ['isResolved' => false],
            ['severity' => 'DESC', 'triggeredAt' => 'DESC'],
            10
        );

        $lowStock = $partRepo->createQueryBuilder('p')
            ->where('p.stockQuantity <= p.minStockAlert')
            ->getQuery()
            ->getResult();

        return $this->render('mechanic/dashboard.html.twig', [
            'pendingInterventions' => $pendingInterventions,
            'alerts' => $alerts,
            'lowStockParts' => $lowStock,
            'lowStockCount' => count($lowStock),
        ]);
    }
}
