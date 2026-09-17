<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $firstName = trim((string) $request->request->get('firstName'));
            $lastName = trim((string) $request->request->get('lastName'));
            $email = trim((string) $request->request->get('email'));

            $newPassword = (string) $request->request->get('newPassword');
            $confirmPassword = (string) $request->request->get('confirmPassword');

            // Validation nom
            if ($firstName === '') {
                $this->addFlash('danger', 'Le prénom est obligatoire.');
                return $this->redirectToRoute('app_profile');
            }

            // Validation prénom
            if ($lastName === '') {
                $this->addFlash('danger', 'Le nom est obligatoire.');
                return $this->redirectToRoute('app_profile');
            }

            // Validation email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('danger', 'L’adresse email est invalide.');
                return $this->redirectToRoute('app_profile');
            }

            // Vérifier que l'email n'est pas déjà utilisé
            $existingUser = $entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($existingUser !== null && $existingUser->getId() !== $user->getId()) {
                $this->addFlash(
                    'danger',
                    'Cette adresse email est déjà utilisée par un autre compte.'
                );

                return $this->redirectToRoute('app_profile');
            }

            // Modifier les informations
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);

            // Changement du mot de passe uniquement si rempli
            if ($newPassword !== '') {
                if (strlen($newPassword) < 8) {
                    $this->addFlash(
                        'danger',
                        'Le nouveau mot de passe doit contenir au moins 8 caractères.'
                    );

                    return $this->redirectToRoute('app_profile');
                }

                if ($newPassword !== $confirmPassword) {
                    $this->addFlash(
                        'danger',
                        'Les deux mots de passe ne correspondent pas.'
                    );

                    return $this->redirectToRoute('app_profile');
                }

                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $newPassword
                );

                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre profil a été modifié avec succès.'
            );

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}