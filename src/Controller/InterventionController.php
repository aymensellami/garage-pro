<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\User;
use App\Form\InterventionType;
use App\Repository\CustomerRepository;
use App\Repository\InterventionRepository;
use App\Repository\VehicleRepository;
use App\Service\InterventionWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/intervention')]
#[IsGranted('ROLE_USER')]
class InterventionController extends AbstractController
{
    #[Route('/', name: 'app_intervention_index', methods: ['GET'])]
    public function index(
        InterventionRepository $repo,
        CustomerRepository $customerRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Admin et mécanicien voient toutes les interventions
        if (
            $this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_MECHANIC')
        ) {
            $interventions = $repo->findBy(
                [],
                ['scheduledAt' => 'DESC']
            );
        } else {
            // Utilisateur normal : uniquement ses interventions
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (!$customer) {
                $interventions = [];
            } else {
                $interventions = $repo->createQueryBuilder('i')
                    ->join('i.vehicle', 'v')
                    ->where('v.owner = :customer')
                    ->setParameter('customer', $customer)
                    ->orderBy('i.scheduledAt', 'DESC')
                    ->getQuery()
                    ->getResult();
            }
        }

        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        VehicleRepository $vehicleRepository,
        CustomerRepository $customerRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Admin et mécanicien : tous les véhicules
        if (
            $this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_MECHANIC')
        ) {
            $vehicles = $vehicleRepository->findBy(
                [],
                ['brand' => 'ASC', 'model' => 'ASC']
            );
        } else {
            // User : uniquement ses véhicules
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (!$customer) {
                $this->addFlash(
                    'warning',
                    'Aucun profil client associé à votre compte.'
                );

                return $this->redirectToRoute('app_dashboard');
            }

            $vehicles = $vehicleRepository->findBy(
                ['owner' => $customer],
                ['brand' => 'ASC', 'model' => 'ASC']
            );
        }

        $intervention = new Intervention();

        $form = $this->createForm(
            InterventionType::class,
            $intervention,
            [
                'vehicles' => $vehicles,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sécurité supplémentaire pour ROLE_USER
            if (
                !$this->isGranted('ROLE_ADMIN') &&
                !$this->isGranted('ROLE_MECHANIC')
            ) {
                $customer = $customerRepository->findOneBy([
                    'email' => $user->getUserIdentifier(),
                ]);

                $vehicle = $intervention->getVehicle();

                if (
                    !$customer ||
                    !$vehicle ||
                    !$vehicle->getOwner() ||
                    $vehicle->getOwner()->getId() !== $customer->getId()
                ) {
                    throw $this->createAccessDeniedException(
                        'Vous ne pouvez créer une intervention que pour votre propre véhicule.'
                    );
                }
            }

            $em->persist($intervention);
            $em->flush();

            $this->addFlash(
                'success',
                'Intervention créée : ' . $intervention->getReference()
            );

            return $this->redirectToRoute(
                'app_intervention_show',
                ['id' => $intervention->getId()]
            );
        }

        return $this->render('intervention/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(
        Intervention $intervention,
        CustomerRepository $customerRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Admin et mécanicien peuvent voir toutes les interventions
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_MECHANIC')
        ) {
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            $vehicle = $intervention->getVehicle();

            if (
                !$customer ||
                !$vehicle ||
                !$vehicle->getOwner() ||
                $vehicle->getOwner()->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException();
            }
        }

        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}/start', name: 'app_intervention_start', methods: ['POST'])]
    #[IsGranted('ROLE_MECHANIC')]
    public function start(
        Intervention $intervention,
        EntityManagerInterface $em
    ): Response {
        $intervention->markAsInProgress();

        $em->flush();

        $this->addFlash(
            'success',
            'Intervention démarrée.'
        );

        return $this->redirectToRoute(
            'app_intervention_show',
            ['id' => $intervention->getId()]
        );
    }

    #[Route('/{id}/complete', name: 'app_intervention_complete', methods: ['POST'])]
    #[IsGranted('ROLE_MECHANIC')]
    public function complete(
        Intervention $intervention,
        InterventionWorkflowService $workflow,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $workflow->complete(
            $intervention,
            $user
        );

        $em->flush();

        $this->addFlash(
            'success',
            'Intervention terminée. Facture générée.'
        );

        return $this->redirectToRoute(
            'app_intervention_show',
            ['id' => $intervention->getId()]
        );
    }
}