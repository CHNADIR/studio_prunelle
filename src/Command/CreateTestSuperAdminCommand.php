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
    name: 'app:create-test-superadmin',
    description: 'Crée un utilisateur test super-admin',
)]
class CreateTestSuperAdminCommand extends Command
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
        $user->setEmail('superadmin@studio.fr');
        $user->setNom('SuperAdmin Test');
        $user->setRoles(['ROLE_SUPERADMIN']);

        // Hasher le mot de passe avec le service approprié
        $plainPassword = 'password'; // Mot de passe en clair
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Sauvegarder en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Utilisateur superadmin@studio.fr créé avec succès! Mot de passe: password");

        return Command::SUCCESS;
    }
}