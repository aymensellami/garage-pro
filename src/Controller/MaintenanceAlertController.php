<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MaintenanceAlert;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\MaintenanceAlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/alert')]
#[IsGranted('ROLE_USER')]
class MaintenanceAlertController extends AbstractController
{
    #[Route('/', name: 'app_alert_index', methods: ['GET'])]
    public function index(
        MaintenanceAlertRepository $repo,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN et MÉCANICIEN :
         * accès à toutes les alertes
         */
        if (
            $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_MECHANIC')
        ) {
            $alerts = $repo->findBy(
                ['isResolved' => false],
                ['severity' => 'DESC', 'triggeredAt' => 'DESC']
            );

            return $this->render('alert/index.html.twig', [
                'alerts' => $alerts,
            ]);
        }

        /*
         * USER :
         * uniquement les alertes de ses propres véhicules
         */
        $customer = $customerRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
        ]);

        if (!$customer) {
            return $this->render('alert/index.html.twig', [
                'alerts' => [],
            ]);
        }

        $alerts = $repo->createQueryBuilder('a')
            ->join('a.vehicle', 'v')
            ->where('v.owner = :customer')
            ->andWhere('a.isResolved = :resolved')
            ->setParameter('customer', $customer)
            ->setParameter('resolved', false)
            ->orderBy('a.severity', 'DESC')
            ->addOrderBy('a.triggeredAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('alert/index.html.twig', [
            'alerts' => $alerts,
        ]);
    }

    #[Route(
        '/{id}/resolve',
        name: 'app_alert_resolve',
        methods: ['POST']
    )]
    public function resolve(
        MaintenanceAlert $alert,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN et MÉCANICIEN :
         * peuvent résoudre toutes les alertes
         */
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_MECHANIC')
        ) {
            /*
             * USER :
             * vérification de son Customer
             */
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            /*
             * Vérification de propriété :
             * l'alerte doit appartenir à un véhicule
             * appartenant au Customer connecté.
             */
            if (
                !$customer
                || !$alert->getVehicle()
                || !$alert->getVehicle()->getOwner()
                || $alert->getVehicle()->getOwner()->getId()
                    !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à résoudre cette alerte.');
            }
        }

        /*
         * Résolution de l'alerte
         */
        $alert->resolve($user);

        $em->flush();

        $this->addFlash(
            'success',
            'Alerte résolue avec succès.'
        );

        return $this->redirectToRoute(
            'app_alert_index'
        );
    }
}
