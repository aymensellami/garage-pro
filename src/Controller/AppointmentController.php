<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\CustomerRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointment')]
#[IsGranted('ROLE_USER')]
class AppointmentController extends AbstractController
{
    /**
     * Liste des rendez-vous.
     *
     * USER :
     * uniquement ses rendez-vous.
     *
     * MECHANIC + ADMIN :
     * tous les rendez-vous.
     */
    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    public function index(
        AppointmentRepository $repo,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        /*
         * ADMIN + MECHANIC :
         * voient tous les rendez-vous.
         */
        if (
            $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_MECHANIC')
        ) {
            $appointments = $repo->findBy(
                [],
                ['scheduledAt' => 'DESC']
            );

            $todayAppointments = $repo->findForDateRange(
                $today,
                $tomorrow
            );

            return $this->render('appointment/index.html.twig', [
                'appointments' => $appointments,
                'todayAppointments' => $todayAppointments,
            ]);
        }

        /*
         * USER :
         * retrouver son Customer grâce à son email.
         */
        $customer = $customerRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
        ]);

        /*
         * Aucun Customer associé.
         */
        if (!$customer) {
            return $this->render('appointment/index.html.twig', [
                'appointments' => [],
                'todayAppointments' => [],
            ]);
        }

        /*
         * USER :
         * récupérer uniquement les rendez-vous
         * liés à ses véhicules.
         *
         * On utilise le QueryBuilder pour filtrer
         * via vehicle.owner.
         */
        $appointments = $repo->createQueryBuilder('a')
            ->join('a.vehicle', 'v')
            ->where('v.owner = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('a.scheduledAt', 'DESC')
            ->getQuery()
            ->getResult();

        /*
         * Rendez-vous du jour de l'utilisateur.
         */
        $todayAppointments = $repo->createQueryBuilder('a')
            ->join('a.vehicle', 'v')
            ->where('v.owner = :customer')
            ->andWhere('a.scheduledAt >= :today')
            ->andWhere('a.scheduledAt < :tomorrow')
            ->setParameter('customer', $customer)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('a.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
            'todayAppointments' => $todayAppointments,
        ]);
    }

    /**
     * Créer un rendez-vous.
     *
     * USER :
     * peut créer un rendez-vous uniquement
     * pour ses propres véhicules.
     *
     * MECHANIC + ADMIN :
     * peuvent créer un rendez-vous pour tous les véhicules.
     */
    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
        VehicleRepository $vehicleRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $appointment = new Appointment();

        /*
         * USER :
         * retrouver son Customer.
         */
        $customer = null;

        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_MECHANIC')
        ) {
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (!$customer) {
                $this->addFlash(
                    'danger',
                    'Votre compte client n\'est pas associé à votre utilisateur.'
                );

                return $this->redirectToRoute(
                    'app_appointment_index'
                );
            }
        }

        /*
         * Création du formulaire.
         */
        $form = $this->createForm(
            AppointmentType::class,
            $appointment
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * Récupérer le véhicule sélectionné.
             */
            $vehicle = $appointment->getVehicle();

            if (!$vehicle) {
                $this->addFlash(
                    'danger',
                    'Veuillez sélectionner un véhicule.'
                );

                return $this->render(
                    'appointment/new.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }

            /*
             * USER :
             * vérification obligatoire que le véhicule
             * appartient bien au Customer connecté.
             */
            if (
                !$this->isGranted('ROLE_ADMIN')
                && !$this->isGranted('ROLE_MECHANIC')
            ) {
                if (
                    !$vehicle->getOwner()
                    || $vehicle->getOwner()->getId() !== $customer?->getId()
                ) {
                    throw $this->createAccessDeniedException('Vous ne pouvez pas créer un rendez-vous pour ce véhicule.');
                }
            }

            /*
             * Enregistrer l'utilisateur qui a créé
             * le rendez-vous.
             */
            $appointment->setCreatedBy($user);

            $em->persist($appointment);
            $em->flush();

            $this->addFlash(
                'success',
                'Rendez-vous planifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_appointment_index'
            );
        }

        return $this->render(
            'appointment/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Confirmer un rendez-vous.
     *
     * ADMIN + MECHANIC :
     * peuvent confirmer tous les rendez-vous.
     *
     * USER :
     * peut uniquement confirmer un rendez-vous
     * lié à son propre véhicule.
     */
    #[Route(
        '/{id}/confirm',
        name: 'app_appointment_confirm',
        methods: ['POST']
    )]
    public function confirm(
        Appointment $appointment,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * USER :
         * vérification de propriété.
         */
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_MECHANIC')
        ) {
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (
                !$customer
                || !$appointment->getVehicle()
                || !$appointment->getVehicle()->getOwner()
                || $appointment->getVehicle()->getOwner()->getId()
                    !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à confirmer ce rendez-vous.');
            }
        }

        $appointment->confirm();

        $em->flush();

        $this->addFlash(
            'success',
            'Rendez-vous confirmé.'
        );

        return $this->redirectToRoute(
            'app_appointment_index'
        );
    }
}