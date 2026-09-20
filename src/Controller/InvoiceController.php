<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/invoice')]
#[IsGranted('ROLE_USER')]
class InvoiceController extends AbstractController
{
    #[Route('/', name: 'app_invoice_index', methods: ['GET'])]
    public function index(
        InvoiceRepository $repo,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN et MÉCANICIEN :
         * accès à toutes les factures.
         */
        if (
            $this->isGranted('ROLE_ADMIN')
            || $this->isGranted('ROLE_MECHANIC')
        ) {
            $invoices = $repo->findBy(
                [],
                ['issuedAt' => 'DESC']
            );

            return $this->render('invoice/index.html.twig', [
                'invoices' => $invoices,
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

        if (!$customer) {
            return $this->render('invoice/index.html.twig', [
                'invoices' => [],
            ]);
        }

        /*
         * USER :
         * Invoice
         *   → Intervention
         *       → Vehicle
         *           → Customer
         */
        $invoices = $repo->createQueryBuilder('i')
            ->join('i.intervention', 'intervention')
            ->join('intervention.vehicle', 'v')
            ->where('v.owner = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('i.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    #[Route('/{id}', name: 'app_invoice_show', methods: ['GET'])]
    public function show(
        Invoice $invoice,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN et MÉCANICIEN :
         * peuvent consulter toutes les factures.
         */
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_MECHANIC')
        ) {
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (!$customer) {
                throw $this->createAccessDeniedException('Client introuvable.');
            }

            /*
             * Invoice
             *   → Intervention
             *       → Vehicle
             *           → Customer
             */
            $intervention = $invoice->getIntervention();

            if (!$intervention) {
                throw $this->createAccessDeniedException('Cette facture n\'est associée à aucune intervention.');
            }

            $vehicle = $intervention->getVehicle();

            if (!$vehicle) {
                throw $this->createAccessDeniedException('Cette intervention n\'est associée à aucun véhicule.');
            }

            $owner = $vehicle->getOwner();

            if (
                !$owner
                || $owner->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à consulter cette facture.');
            }
        }

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route(
        '/{id}/pay',
        name: 'app_invoice_pay',
        methods: ['POST']
    )]
    public function pay(
        Request $request,
        Invoice $invoice,
        EntityManagerInterface $em,
        CustomerRepository $customerRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        /*
         * ADMIN et MÉCANICIEN :
         * peuvent enregistrer un paiement sur toutes les factures.
         *
         * USER :
         * uniquement ses propres factures.
         */
        if (
            !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_MECHANIC')
        ) {
            $customer = $customerRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
            ]);

            if (!$customer) {
                throw $this->createAccessDeniedException('Client introuvable.');
            }

            $intervention = $invoice->getIntervention();

            if (!$intervention) {
                throw $this->createAccessDeniedException('Cette facture n\'est associée à aucune intervention.');
            }

            $vehicle = $intervention->getVehicle();

            if (!$vehicle) {
                throw $this->createAccessDeniedException('Cette intervention n\'est associée à aucun véhicule.');
            }

            $owner = $vehicle->getOwner();

            if (
                !$owner
                || $owner->getId() !== $customer->getId()
            ) {
                throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à payer cette facture.');
            }
        }

        /*
         * Récupération du montant.
         */
        $amount = $request->request->get('amount');

        if (null === $amount || !\is_numeric($amount)) {
            $this->addFlash(
                'danger',
                'Le montant du paiement est invalide.'
            );

            return $this->redirectToRoute(
                'app_invoice_show',
                ['id' => $invoice->getId()]
            );
        }

        $amount = (float) $amount;

        if ($amount <= 0) {
            $this->addFlash(
                'danger',
                'Le montant du paiement doit être supérieur à zéro.'
            );

            return $this->redirectToRoute(
                'app_invoice_show',
                ['id' => $invoice->getId()]
            );
        }

        /*
         * Vérifier le montant restant.
         */
        $remainingAmount = (float) $invoice->getRemainingAmount();

        if ($amount > $remainingAmount) {
            $this->addFlash(
                'danger',
                'Le montant du paiement dépasse le montant restant.'
            );

            return $this->redirectToRoute(
                'app_invoice_show',
                ['id' => $invoice->getId()]
            );
        }

        $method = $request->request->get(
            'method',
            'cash'
        );

        /*
         * Création du paiement.
         */
        $payment = new Payment();

        $payment->setInvoice($invoice);
        $payment->setAmount(\number_format($amount, 2, '.', ''));
        $payment->setMethod($method);

        $em->persist($payment);

        /*
         * Mise à jour du statut.
         */
        if ($invoice->getRemainingAmount() <= $amount) {
            $invoice->markAsPaid();
        } else {
            $invoice->setStatus(
                Invoice::STATUS_PARTIALLY_PAID
            );
        }

        $em->flush();

        $this->addFlash(
            'success',
            'Paiement enregistré avec succès.'
        );

        return $this->redirectToRoute(
            'app_invoice_show',
            ['id' => $invoice->getId()]
        );
    }
}