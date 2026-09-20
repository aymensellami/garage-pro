<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\Customer;
use App\Entity\Intervention;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /*
         * ============================================================
         * 1. RÉCUPÉRER UN UTILISATEUR POUR LES RENDEZ-VOUS
         * ============================================================
         */

        $userRepository = $manager->getRepository(User::class);

        $createdBy = $userRepository->findOneBy([
            'email' => 'user@garagepro.tn',
        ]);

        if (!$createdBy) {
            $createdBy = $userRepository->findOneBy([]);
        }

        /*
         * ============================================================
         * 2. RÉCUPÉRER UN MÉCANICIEN
         * ============================================================
         */

        $mechanic = $userRepository->findOneBy([
            'email' => 'mecanicien@garagepro.tn',
        ]);

        if (!$mechanic) {
            $mechanic = $userRepository->findOneBy([
                'roles' => '%ROLE_MECHANIC%',
            ]);
        }

        /*
         * Si aucun mécanicien n'existe, on arrête avec un message clair.
         */
        if (!$mechanic) {
            throw new \RuntimeException('Aucun mécanicien trouvé. Crée d’abord mecanicien@garagepro.tn avec ROLE_MECHANIC.');
        }

        /*
         * ============================================================
         * 3. CLIENTS
         * ============================================================
         */

        $customersData = [
            [
                'firstName' => 'Ahmed',
                'lastName' => 'Ben Ali',
                'email' => 'ahmed.benali@gmail.com',
                'phone' => '22123456',
                'address' => '12 Rue de la Liberté',
                'city' => 'Tunis',
                'postalCode' => '1000',
            ],
            [
                'firstName' => 'Mohamed',
                'lastName' => 'Trabelsi',
                'email' => 'mohamed.trabelsi@gmail.com',
                'phone' => '22345678',
                'address' => '25 Avenue Habib Bourguiba',
                'city' => 'Tunis',
                'postalCode' => '1001',
            ],
            [
                'firstName' => 'Sami',
                'lastName' => 'Jaziri',
                'email' => 'sami.jaziri@gmail.com',
                'phone' => '22456789',
                'address' => '8 Rue de Marseille',
                'city' => 'Ariana',
                'postalCode' => '2080',
            ],
            [
                'firstName' => 'Yassine',
                'lastName' => 'Mansouri',
                'email' => 'yassine.mansouri@gmail.com',
                'phone' => '22567890',
                'address' => '17 Rue Ibn Khaldoun',
                'city' => 'Manouba',
                'postalCode' => '2010',
            ],
            [
                'firstName' => 'Karim',
                'lastName' => 'Mejri',
                'email' => 'karim.mejri@gmail.com',
                'phone' => '22678901',
                'address' => '31 Rue du Lac',
                'city' => 'Les Berges du Lac',
                'postalCode' => '1053',
            ],
            [
                'firstName' => 'Walid',
                'lastName' => 'Khelifi',
                'email' => 'walid.khelifi@gmail.com',
                'phone' => '22789012',
                'address' => '14 Rue de Carthage',
                'city' => 'Ben Arous',
                'postalCode' => '2013',
            ],
            [
                'firstName' => 'Nabil',
                'lastName' => 'Gharbi',
                'email' => 'nabil.gharbi@gmail.com',
                'phone' => '22890123',
                'address' => '6 Rue de Sousse',
                'city' => 'La Marsa',
                'postalCode' => '2070',
            ],
            [
                'firstName' => 'Hatem',
                'lastName' => 'Saidi',
                'email' => 'hatem.saidi@gmail.com',
                'phone' => '22901234',
                'address' => '22 Rue de Bizerte',
                'city' => 'Bizerte',
                'postalCode' => '7000',
            ],
            [
                'firstName' => 'Amine',
                'lastName' => 'Chaabane',
                'email' => 'amine.chaabane@gmail.com',
                'phone' => '23012345',
                'address' => '10 Rue de l\'Indépendance',
                'city' => 'Nabeul',
                'postalCode' => '8000',
            ],
            [
                'firstName' => 'Slim',
                'lastName' => 'Mabrouk',
                'email' => 'slim.mabrouk@gmail.com',
                'phone' => '23123456',
                'address' => '19 Rue de la République',
                'city' => 'Sousse',
                'postalCode' => '4000',
            ],
        ];

        $customers = [];

        foreach ($customersData as $data) {
            $customer = $manager
                ->getRepository(Customer::class)
                ->findOneBy([
                    'email' => $data['email'],
                ]);

            if (!$customer) {
                $customer = new Customer();

                $customer
                    ->setFirstName($data['firstName'])
                    ->setLastName($data['lastName'])
                    ->setEmail($data['email'])
                    ->setPhone($data['phone'])
                    ->setAddress($data['address'])
                    ->setCity($data['city'])
                    ->setPostalCode($data['postalCode']);

                $manager->persist($customer);
            }

            $customers[] = $customer;
        }

        $manager->flush();

        /*
         * ============================================================
         * 4. VÉHICULES
         * ============================================================
         */

        $vehiclesData = [
            [
                'brand' => 'Hyundai',
                'model' => 'i20',
                'registration' => '123 TUN 4567',
                'vin' => 'KMH12345678900001',
                'year' => 2021,
                'mileage' => 45000,
                'fuelType' => 'Essence',
                'engineCode' => 'G4LC',
                'color' => 'Blanc',
            ],
            [
                'brand' => 'Hyundai',
                'model' => 'Tucson',
                'registration' => '234 TUN 5678',
                'vin' => 'KMH12345678900002',
                'year' => 2020,
                'mileage' => 68000,
                'fuelType' => 'Diesel',
                'engineCode' => 'D4HA',
                'color' => 'Noir',
            ],
            [
                'brand' => 'Renault',
                'model' => 'Clio',
                'registration' => '345 TUN 6789',
                'vin' => 'VF123456789000003',
                'year' => 2019,
                'mileage' => 72000,
                'fuelType' => 'Essence',
                'engineCode' => 'H5F',
                'color' => 'Gris',
            ],
            [
                'brand' => 'Peugeot',
                'model' => '208',
                'registration' => '456 TUN 7890',
                'vin' => 'VF123456789000004',
                'year' => 2022,
                'mileage' => 28000,
                'fuelType' => 'Essence',
                'engineCode' => 'EB2',
                'color' => 'Bleu',
            ],
            [
                'brand' => 'Volkswagen',
                'model' => 'Golf 8',
                'registration' => '567 TUN 8901',
                'vin' => 'WVW12345678900005',
                'year' => 2021,
                'mileage' => 39000,
                'fuelType' => 'Diesel',
                'engineCode' => 'DADA',
                'color' => 'Noir',
            ],
            [
                'brand' => 'Toyota',
                'model' => 'Yaris',
                'registration' => '678 TUN 9012',
                'vin' => 'JT123456789000006',
                'year' => 2020,
                'mileage' => 51000,
                'fuelType' => 'Hybride',
                'engineCode' => '2ZR',
                'color' => 'Rouge',
            ],
            [
                'brand' => 'Kia',
                'model' => 'Sportage',
                'registration' => '789 TUN 0123',
                'vin' => 'KNA12345678900007',
                'year' => 2022,
                'mileage' => 31000,
                'fuelType' => 'Diesel',
                'engineCode' => 'D4HE',
                'color' => 'Gris',
            ],
            [
                'brand' => 'Ford',
                'model' => 'Focus',
                'registration' => '890 TUN 1234',
                'vin' => 'WF01234567890008',
                'year' => 2018,
                'mileage' => 95000,
                'fuelType' => 'Diesel',
                'engineCode' => 'TDCI',
                'color' => 'Blanc',
            ],
            [
                'brand' => 'Dacia',
                'model' => 'Duster',
                'registration' => '901 TUN 2345',
                'vin' => 'UU12345678900009',
                'year' => 2021,
                'mileage' => 47000,
                'fuelType' => 'Diesel',
                'engineCode' => 'K9K',
                'color' => 'Marron',
            ],
            [
                'brand' => 'Hyundai',
                'model' => 'i10',
                'registration' => '012 TUN 3456',
                'vin' => 'KMH12345678900010',
                'year' => 2023,
                'mileage' => 18000,
                'fuelType' => 'Essence',
                'engineCode' => 'G3LA',
                'color' => 'Rouge',
            ],
        ];

        $vehicles = [];

        foreach ($vehiclesData as $index => $data) {
            $customer = $customers[$index];

            $vehicle = $manager
                ->getRepository(Vehicle::class)
                ->findOneBy([
                    'registration' => $data['registration'],
                ]);

            if (!$vehicle) {
                $vehicle = new Vehicle();

                $vehicle
                    ->setBrand($data['brand'])
                    ->setModel($data['model'])
                    ->setRegistration($data['registration'])
                    ->setVin($data['vin'])
                    ->setYear($data['year'])
                    ->setMileage($data['mileage'])
                    ->setFuelType($data['fuelType'])
                    ->setEngineCode($data['engineCode'])
                    ->setColor($data['color'])
                    ->setOwner($customer);

                $manager->persist($vehicle);
            } else {
                $vehicle->setOwner($customer);
            }

            $vehicles[] = $vehicle;
        }

        $manager->flush();

        /*
         * ============================================================
         * 5. RENDEZ-VOUS
         * ============================================================
         */

        $appointmentsData = [
            [
                'vehicle' => 0,
                'date' => '+1 day 09:00',
                'duration' => 60,
                'reason' => 'Vidange et contrôle général',
                'status' => 'requested',
                'notes' => 'Prévoir contrôle des niveaux.',
            ],
            [
                'vehicle' => 1,
                'date' => '+2 days 10:30',
                'duration' => 90,
                'reason' => 'Révision périodique',
                'status' => 'confirmed',
                'notes' => 'Révision des 70 000 km.',
            ],
            [
                'vehicle' => 2,
                'date' => '+3 days 14:00',
                'duration' => 120,
                'reason' => 'Diagnostic moteur',
                'status' => 'confirmed',
                'notes' => 'Voyant moteur allumé.',
            ],
            [
                'vehicle' => 3,
                'date' => '+4 days 09:30',
                'duration' => 90,
                'reason' => 'Changement des freins',
                'status' => 'requested',
                'notes' => 'Contrôle plaquettes et disques.',
            ],
            [
                'vehicle' => 4,
                'date' => '+5 days 11:00',
                'duration' => 120,
                'reason' => 'Révision complète',
                'status' => 'confirmed',
                'notes' => 'Révision avant long trajet.',
            ],
            [
                'vehicle' => 5,
                'date' => '+6 days 15:00',
                'duration' => 60,
                'reason' => 'Contrôle climatisation',
                'status' => 'requested',
                'notes' => 'Climatisation moins efficace.',
            ],
            [
                'vehicle' => 6,
                'date' => '+7 days 09:00',
                'duration' => 120,
                'reason' => 'Entretien système de freinage',
                'status' => 'confirmed',
                'notes' => null,
            ],
            [
                'vehicle' => 7,
                'date' => '+8 days 13:30',
                'duration' => 90,
                'reason' => 'Diagnostic électronique',
                'status' => 'requested',
                'notes' => 'Vérification électronique complète.',
            ],
        ];

        foreach ($appointmentsData as $data) {
            $vehicle = $vehicles[$data['vehicle']];
            $scheduledAt = new \DateTime($data['date']);

            $appointment = new Appointment();

            $appointment
                ->setVehicle($vehicle)
                ->setScheduledAt($scheduledAt)
                ->setDuration($data['duration'])
                ->setReason($data['reason'])
                ->setStatus($data['status'])
                ->setNotes($data['notes'])
                ->setReminderSent(false)
                ->setCreatedBy($createdBy);

            $manager->persist($appointment);
        }

        $manager->flush();

        /*
         * ============================================================
         * 6. INTERVENTIONS
         * ============================================================
         */

        $interventionsData = [
            [
                'vehicle' => 0,
                'description' => 'Vidange moteur et remplacement du filtre à huile',
                'operations' => [
                    'Vidange huile moteur',
                    'Remplacement filtre à huile',
                    'Contrôle des niveaux',
                ],
                'estimatedCost' => '180.00',
                'finalCost' => '175.00',
                'status' => Intervention::STATUS_COMPLETED,
                'scheduledAt' => '-10 days 09:00',
                'duration' => 60,
                'notes' => 'Intervention terminée avec succès.',
            ],
            [
                'vehicle' => 1,
                'description' => 'Révision périodique',
                'operations' => [
                    'Vidange',
                    'Filtre à huile',
                    'Filtre à air',
                    'Contrôle freins',
                ],
                'estimatedCost' => '420.00',
                'finalCost' => '450.00',
                'status' => Intervention::STATUS_INVOICED,
                'scheduledAt' => '-8 days 10:00',
                'duration' => 120,
                'notes' => 'Véhicule prêt.',
            ],
            [
                'vehicle' => 2,
                'description' => 'Diagnostic voyant moteur',
                'operations' => [
                    'Diagnostic électronique',
                    'Lecture des codes défaut',
                    'Contrôle capteurs',
                ],
                'estimatedCost' => '150.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_IN_PROGRESS,
                'scheduledAt' => '-2 days 14:00',
                'duration' => 90,
                'notes' => 'Diagnostic en cours.',
            ],
            [
                'vehicle' => 3,
                'description' => 'Remplacement plaquettes de frein avant',
                'operations' => [
                    'Démontage roues avant',
                    'Remplacement plaquettes',
                    'Contrôle freinage',
                ],
                'estimatedCost' => '280.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_WAITING_PARTS,
                'scheduledAt' => '-1 day 09:30',
                'duration' => 90,
                'notes' => 'En attente des pièces.',
            ],
            [
                'vehicle' => 4,
                'description' => 'Révision complète 40 000 km',
                'operations' => [
                    'Vidange',
                    'Filtres',
                    'Contrôle batterie',
                    'Contrôle pneumatiques',
                ],
                'estimatedCost' => '500.00',
                'finalCost' => '520.00',
                'status' => Intervention::STATUS_COMPLETED,
                'scheduledAt' => '-20 days 11:00',
                'duration' => 150,
                'notes' => 'Révision terminée.',
            ],
            [
                'vehicle' => 5,
                'description' => 'Entretien climatisation',
                'operations' => [
                    'Contrôle circuit',
                    'Contrôle pression',
                    'Recharge climatisation',
                ],
                'estimatedCost' => '220.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_PENDING,
                'scheduledAt' => '+1 day 15:00',
                'duration' => 60,
                'notes' => 'Intervention planifiée.',
            ],
            [
                'vehicle' => 6,
                'description' => 'Contrôle système de freinage',
                'operations' => [
                    'Contrôle plaquettes',
                    'Contrôle disques',
                    'Contrôle liquide de frein',
                ],
                'estimatedCost' => '180.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_QUALITY_CHECK,
                'scheduledAt' => '-3 days 09:00',
                'duration' => 90,
                'notes' => 'Contrôle qualité avant restitution.',
            ],
            [
                'vehicle' => 7,
                'description' => 'Diagnostic électronique complet',
                'operations' => [
                    'Diagnostic ECU',
                    'Lecture défauts',
                    'Test batterie',
                ],
                'estimatedCost' => '190.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_PENDING,
                'scheduledAt' => '+2 days 13:30',
                'duration' => 90,
                'notes' => 'En attente de prise en charge.',
            ],
            [
                'vehicle' => 8,
                'description' => 'Remplacement filtre à carburant',
                'operations' => [
                    'Remplacement filtre',
                    'Contrôle alimentation carburant',
                ],
                'estimatedCost' => '210.00',
                'finalCost' => '205.00',
                'status' => Intervention::STATUS_COMPLETED,
                'scheduledAt' => '-15 days 10:00',
                'duration' => 75,
                'notes' => 'Intervention terminée.',
            ],
            [
                'vehicle' => 9,
                'description' => 'Entretien général',
                'operations' => [
                    'Vidange',
                    'Contrôle niveaux',
                    'Contrôle pneumatiques',
                ],
                'estimatedCost' => '160.00',
                'finalCost' => null,
                'status' => Intervention::STATUS_PENDING,
                'scheduledAt' => '+3 days 09:00',
                'duration' => 60,
                'notes' => 'Intervention planifiée.',
            ],
        ];

        foreach ($interventionsData as $data) {
            $vehicle = $vehicles[$data['vehicle']];

            $intervention = new Intervention();

            $intervention
                ->setVehicle($vehicle)
                ->setMechanic($mechanic)
                ->setDescription($data['description'])
                ->setOperations($data['operations'])
                ->setEstimatedCost($data['estimatedCost'])
                ->setFinalCost($data['finalCost'])
                ->setStatus($data['status'])
                ->setScheduledAt(new \DateTime($data['scheduledAt']))
                ->setDurationMinutes($data['duration'])
                ->setNotes($data['notes']);

            if (Intervention::STATUS_IN_PROGRESS === $data['status']) {
                $intervention->setStartedAt(new \DateTime('-2 days 14:30'));
            }

            if (Intervention::STATUS_COMPLETED === $data['status']) {
                $intervention
                    ->setStartedAt(new \DateTime('-10 days 09:00'))
                    ->setCompletedAt(new \DateTime('-10 days 10:00'));
            }

            $manager->persist($intervention);
        }

        $manager->flush();

        /*
         * ============================================================
         * FIN
         * ============================================================
         */

        echo \PHP_EOL;
        echo '============================================='.\PHP_EOL;
        echo 'GaragePro Fixtures chargées avec succès !'.\PHP_EOL;
        echo '============================================='.\PHP_EOL;
        echo 'Clients       : 10'.\PHP_EOL;
        echo 'Véhicules     : 10'.\PHP_EOL;
        echo 'Rendez-vous   : 8'.\PHP_EOL;
        echo 'Interventions : 10'.\PHP_EOL;
        echo '============================================='.\PHP_EOL;
    }
}