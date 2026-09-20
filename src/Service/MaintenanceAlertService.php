<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MaintenanceAlert;
use App\Entity\Vehicle;
use App\Repository\MaintenanceAlertRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;

class MaintenanceAlertService
{
    public function __construct(
        private VehicleRepository $vehicleRepo,
        private MaintenanceAlertRepository $alertRepo,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @return MaintenanceAlert[]
     */
    public function checkAll(): array
    {
        $alerts = [];
        $vehicles = $this->vehicleRepo->findAll();

        foreach ($vehicles as $vehicle) {
            $alerts = \array_merge($alerts, $this->checkVehicle($vehicle));
        }

        return $alerts;
    }

    /**
     * @return MaintenanceAlert[]
     */
    public function checkVehicle(Vehicle $vehicle): array
    {
        $alerts = [];

        // CT
        if (!$vehicle->isTechnicalControlValid()) {
            $alerts[] = $this->createAlert($vehicle, MaintenanceAlert::TYPE_TECHNICAL_CONTROL, MaintenanceAlert::SEVERITY_DANGER, 'CT expiré ou proche');
        } elseif ($vehicle->getTechnicalControlDate()) {
            $deadline = \DateTimeImmutable::createFromInterface($vehicle->getTechnicalControlDate())->modify('+23 months');
            if ($deadline < new \DateTimeImmutable('+30 days')) {
                $alerts[] = $this->createAlert($vehicle, MaintenanceAlert::TYPE_TECHNICAL_CONTROL, MaintenanceAlert::SEVERITY_WARNING, 'CT à renouveler dans moins de 30 jours');
            }
        }

        // Vidange
        $lastOilChange = $vehicle->getLastOilChange();
        if ($lastOilChange) {
            $kmSince = $vehicle->getMileage() - ($lastOilChange->getVehicle()->getMileage() ?? 0);
            $monthsSince = $lastOilChange->getScheduledAt()->diff(new \DateTime())->m + ($lastOilChange->getScheduledAt()->diff(new \DateTime())->y * 12);
            if ($kmSince > 15000 || $monthsSince >= 12) {
                $alerts[] = $this->createAlert($vehicle, MaintenanceAlert::TYPE_OIL_CHANGE, MaintenanceAlert::SEVERITY_WARNING, 'Vidange due');
            }
        }

        return $alerts;
    }

    private function createAlert(Vehicle $vehicle, string $type, string $severity, string $message): MaintenanceAlert
    {
        $existing = $this->alertRepo->findOneBy([
            'vehicle' => $vehicle,
            'type' => $type,
            'isResolved' => false,
        ]);

        if ($existing) {
            return $existing;
        }

        $alert = new MaintenanceAlert();
        $alert->setVehicle($vehicle);
        $alert->setType($type);
        $alert->setSeverity($severity);
        $alert->setMessage($message);
        $this->em->persist($alert);
        $this->em->flush();

        return $alert;
    }
}
