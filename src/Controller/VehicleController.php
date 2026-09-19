<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Repository\CustomerRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/vehicle')]
#[IsGranted('ROLE_USER')]
class VehicleController extends AbstractController
{
    /**
     * Liste des véhicules.
     *
     * USER :
     *   uniquement ses propres véhicules.
     *
     * MECHANIC :
     *   tous les véhicules.
     *
     * ADMIN :
     *   tous les véhicules.
     */
    #[Route('/', name: 'app_vehicle_index', methods: ['GET'])]
    public function index(
        VehicleRepository $repo,
        CustomerRepository $customerRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN + MECHANIC :
         * accès à tous les véhicules.
         */
        if (
            $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_MECHANIC')
        ) {
            $vehicles = $repo->findBy(
                [],
                ['createdAt' => 'DESC']
            );

            return $this->render('vehicle/index.html.twig', [
                'vehicles' => $vehicles,
            ]);
        }

        /*
         * USER :
         * recherche du Customer correspondant
         * à l'utilisateur connecté.
         */
        $customer = $customerRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
        ]);

        /*
         * Aucun profil Customer associé.
         */
        if (!$customer) {
            return $this->render('vehicle/index.html.twig', [
                'vehicles' => [],
            ]);
        }

        /*
         * USER :
         * uniquement ses propres véhicules.
         */
        $vehicles = $repo->findBy(
            ['owner' => $customer],
            ['createdAt' => 'DESC']
        );

        return $this->render('vehicle/index.html.twig', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Ajouter un véhicule.
     *
     * ADMIN + MECHANIC :
     * peuvent ajouter un véhicule pour n'importe quel client
     * selon les possibilités de VehicleType.
     *
     * USER :
     * le véhicule est automatiquement associé
     * à son Customer.
     */
    #[Route('/new', name: 'app_vehicle_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $vehicle = new Vehicle();

        /*
         * USER :
         * association automatique avec son Customer.
         *
         * ADMIN + MECHANIC :
         * aucune association automatique.
         * Le formulaire peut permettre de sélectionner
         * le propriétaire.
         */
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

                return $this->redirectToRoute('app_vehicle_index');
            }

            $vehicle->setOwner($customer);
        }

        $form = $this->createForm(
            VehicleType::class,
            $vehicle
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * USER :
             * sécurité supplémentaire.
             *
             * Même si le formulaire contient un champ owner,
             * le propriétaire sera toujours le Customer connecté.
             */
            if (
                !$this->isGranted('ROLE_ADMIN')
                && !$this->isGranted('ROLE_MECHANIC')
            ) {
                $customer = $customerRepository->findOneBy([
                    'email' => $user->getUserIdentifier(),
                ]);

                if (!$customer) {
                    throw $this->createAccessDeniedException();
                }

                $vehicle->setOwner($customer);
            }

            $em->persist($vehicle);
            $em->flush();

            $this->addFlash(
                'success',
                'Véhicule enregistré avec succès.'
            );

            return $this->redirectToRoute(
                'app_vehicle_index'
            );
        }

        return $this->render('vehicle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Afficher un véhicule.
     *
     * USER :
     * uniquement ses propres véhicules.
     *
     * MECHANIC + ADMIN :
     * tous les véhicules.
     */
    #[Route('/{id}', name: 'app_vehicle_show', methods: ['GET'])]
    public function show(
        Vehicle $vehicle,
        CustomerRepository $customerRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * Seul un USER normal doit avoir
         * une vérification de propriétaire.
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
                || !$vehicle->getOwner()
                || $vehicle->getOwner()->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter ce véhicule.');
            }
        }

        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'history' => $vehicle->getInterventions(),
        ]);
    }

    /**
     * Modifier un véhicule.
     *
     * USER :
     * uniquement ses propres véhicules.
     *
     * MECHANIC + ADMIN :
     * peuvent modifier tous les véhicules.
     */
    #[Route('/{id}/edit', name: 'app_vehicle_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Vehicle $vehicle,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * USER :
         * vérification du propriétaire.
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
                || !$vehicle->getOwner()
                || $vehicle->getOwner()->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce véhicule.');
            }
        }

        /*
         * Mémorisation du propriétaire.
         *
         * Pour un USER, cela empêche de modifier
         * le propriétaire avec le formulaire.
         */
        $owner = $vehicle->getOwner();

        $form = $this->createForm(
            VehicleType::class,
            $vehicle
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
             * USER :
             * conserve obligatoirement son propriétaire.
             *
             * ADMIN + MECHANIC :
             * peuvent modifier le propriétaire si
             * VehicleType le permet.
             */
            if (
                !$this->isGranted('ROLE_ADMIN')
                && !$this->isGranted('ROLE_MECHANIC')
            ) {
                $vehicle->setOwner($owner);
            }

            $em->flush();

            $this->addFlash(
                'success',
                'Véhicule mis à jour.'
            );

            return $this->redirectToRoute(
                'app_vehicle_show',
                [
                    'id' => $vehicle->getId(),
                ]
            );
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprimer un véhicule.
     *
     * USER :
     * uniquement ses propres véhicules.
     *
     * MECHANIC + ADMIN :
     * tous les véhicules.
     */
    #[Route('/{id}', name: 'app_vehicle_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Vehicle $vehicle,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * Vérification CSRF.
         */
        if (
            !$this->isCsrfTokenValid(
                'delete'.$vehicle->getId(),
                $request->request->get('_token')
            )
        ) {
            $this->addFlash(
                'danger',
                'Jeton de sécurité invalide.'
            );

            return $this->redirectToRoute(
                'app_vehicle_index'
            );
        }

        /*
         * USER :
         * peut uniquement supprimer son propre véhicule.
         *
         * MECHANIC + ADMIN :
         * peuvent supprimer tous les véhicules.
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
                || !$vehicle->getOwner()
                || $vehicle->getOwner()->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce véhicule.');
            }
        }

        $em->remove($vehicle);
        $em->flush();

        $this->addFlash(
            'success',
            'Véhicule supprimé.'
        );

        return $this->redirectToRoute(
            'app_vehicle_index'
        );
    }
}
