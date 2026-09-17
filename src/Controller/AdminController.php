<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\InterventionRepository;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use App\Service\AdminStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /**
     * Dashboard principal de l'administration
     */
    #[Route('/panel', name: 'app_admin_dashboard', methods: ['GET'])]
    public function dashboard(
        AdminStatsService $statsService,
        UserRepository $userRepo,
        InterventionRepository $interventionRepo,
        InvoiceRepository $invoiceRepo
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'globalStats' => $statsService->getGlobalStats(),

            'revenueByMonth' => $statsService->getRevenueByMonth(12),

            'topCustomers' => $statsService->getTopCustomers(10),

            'topMechanics' => $statsService->getTopMechanics(10),

            'recentUsers' => $userRepo->findBy(
                [],
                ['createdAt' => 'DESC'],
                5
            ),

            'pendingInvoices' => $invoiceRepo->findBy(
                ['status' => 'issued'],
                ['issuedAt' => 'DESC'],
                5
            ),

            'interventionsByStatus' => $interventionRepo->countByStatus(),
        ]);
    }

    /**
     * Liste des utilisateurs
     */
    #[Route('/users', name: 'app_admin_users', methods: ['GET'])]
    public function users(UserRepository $repo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $repo->findBy(
                [],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    /**
     * Création d'un utilisateur
     */
    #[Route(
        '/users/new',
        name: 'app_admin_user_new',
        methods: ['GET', 'POST']
    )]
    public function newUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();

        $form = $this->createForm(
            AdminUserType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form
                ->get('plainPassword')
                ->getData();

            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Utilisateur créé : ' . $user->getEmail()
            );

            return $this->redirectToRoute(
                'app_admin_users'
            );
        }

        return $this->render(
            'admin/user_form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Nouvel utilisateur',
            ]
        );
    }

    /**
     * Modification d'un utilisateur
     */
    #[Route(
        '/users/{id}/edit',
        name: 'app_admin_user_edit',
        methods: ['GET', 'POST']
    )]
    public function editUser(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(
            AdminUserType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form
                ->get('plainPassword')
                ->getData();

            /*
             * On change le mot de passe uniquement
             * si l'administrateur en saisit un nouveau.
             */
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }

            $em->flush();

            $this->addFlash(
                'success',
                'Utilisateur mis à jour.'
            );

            return $this->redirectToRoute(
                'app_admin_users'
            );
        }

        return $this->render(
            'admin/user_form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Modifier ' . $user->getFullName(),
                'user' => $user,
            ]
        );
    }

    /**
     * Activer / désactiver un utilisateur
     */
    #[Route(
        '/users/{id}/toggle',
        name: 'app_admin_user_toggle',
        methods: ['POST']
    )]
    public function toggleUser(
        User $user,
        EntityManagerInterface $em
    ): Response {
        $user->setIsActive(
            !$user->isActive()
        );

        $em->flush();

        $this->addFlash(
            'success',
            'Statut de l\'utilisateur mis à jour.'
        );

        return $this->redirectToRoute(
            'app_admin_users'
        );
    }

    /**
     * Statistiques détaillées
     */
    #[Route('/stats', name: 'app_admin_stats', methods: ['GET'])]
    public function stats(
        AdminStatsService $statsService
    ): Response {
        return $this->render(
            'admin/stats.html.twig',
            [
                'revenueByMonth' => $statsService
                    ->getRevenueByMonth(12),

                'interventionsByMonth' => $statsService
                    ->getInterventionsByMonth(12),

                'partsByCategory' => $statsService
                    ->getPartsByCategory(),

                'customerAcquisition' => $statsService
                    ->getCustomerAcquisition(12),

                'stockValue' => $statsService
                    ->getStockValue(),

                'avgInvoiceAmount' => $statsService
                    ->getAverageInvoiceAmount(),
            ]
        );
    }

    /**
     * Paramètres d'administration
     */
    #[Route('/settings', name: 'app_admin_settings', methods: ['GET'])]
    public function settings(): Response
    {
        return $this->render(
            'admin/settings.html.twig'
        );
    }
}