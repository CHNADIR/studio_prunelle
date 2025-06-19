<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-admin',
    description: 'Crée un utilisateur test admin',
)]
class CreateTestAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Créer l'utilisateur
        $user = new User();
        $user->setEmail('admin@studio.fr');
        $user->setNom('Admin Test');
        $user->setRoles(['ROLE_ADMIN']);

        // Hasher le mot de passe avec le service approprié
        $plainPassword = 'password'; // Mot de passe en clair
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Sauvegarder en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Utilisateur admin@studio.fr créé avec succès! Mot de passe: password");

        return Command::SUCCESS;
    }
}