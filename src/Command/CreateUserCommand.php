<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un utilisateur GaragePro'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email')
            ->addArgument('firstName', InputArgument::REQUIRED, 'Prénom')
            ->addArgument('lastName', InputArgument::REQUIRED, 'Nom')
            ->addArgument(
                'role',
                InputArgument::OPTIONAL,
                'Rôle: ROLE_USER, ROLE_MECHANIC ou ROLE_ADMIN',
                User::ROLE_USER
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $email = $input->getArgument('email');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');
        $role = $input->getArgument('role');

        $validRoles = [
            User::ROLE_USER,
            User::ROLE_MECHANIC,
            User::ROLE_ADMIN,
        ];

        if (!\in_array($role, $validRoles, true)) {
            $output->writeln('<error>Rôle invalide.</error>');
            $output->writeln(
                'Rôles disponibles : ROLE_USER, ROLE_MECHANIC, ROLE_ADMIN'
            );

            return Command::FAILURE;
        }

        $repository = $this->entityManager->getRepository(User::class);

        if ($repository->findOneBy(['email' => $email])) {
            $output->writeln(
                '<error>Un utilisateur avec cet email existe déjà.</error>'
            );

            return Command::FAILURE;
        }

        // Demande sécurisée du mot de passe
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Mot de passe : ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        if (!$password) {
            $output->writeln('<error>Le mot de passe est obligatoire.</error>');

            return Command::FAILURE;
        }

        $user = new User();

        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([$role]);
        $user->setIsActive(true);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('');
        $output->writeln('<info>Utilisateur créé avec succès !</info>');
        $output->writeln("Email : {$email}");
        $output->writeln("Nom : {$firstName} {$lastName}");
        $output->writeln("Rôle : {$role}");

        return Command::SUCCESS;
    }
}
