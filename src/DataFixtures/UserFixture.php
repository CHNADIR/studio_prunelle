<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface; // Importer LoggerInterface
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger; // Ajouter la propriété logger

    // Injection du service de hachage de mot de passe et du logger
    public function __construct(UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger)
    {
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger; // Initialiser le logger
    }

    public function load(ObjectManager $manager): void
    {
        $this->logger->info('[UserFixture] Début du chargement des utilisateurs.');

        // Création de l'administrateur
        $admin = new User();
        $adminEmail = 'admin@studioprunelle.fr';
        $adminPassword = 'password';
        $admin->setEmail($adminEmail);
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPasswordAdmin = $this->passwordHasher->hashPassword($admin, $adminPassword);
        $admin->setPassword($hashedPasswordAdmin);
        $manager->persist($admin);
        $this->logger->info('[UserFixture] Admin créé : {email}, mot de passe haché (longueur) : {length}', [
            'email' => $adminEmail,
            'length' => strlen($hashedPasswordAdmin)
        ]);

        // Création de l'utilisateur standard
        $user = new User();
        $userEmail = 'user@studioprunelle.fr';
        $userPassword = 'password';
        $user->setEmail($userEmail);
        // $user->setRoles(['ROLE_USER']); // Pas strictement nécessaire car getRoles() ajoute ROLE_USER
        $hashedPasswordUser = $this->passwordHasher->hashPassword($user, $userPassword);
        $user->setPassword($hashedPasswordUser);
        $manager->persist($user);
        $this->logger->info('[UserFixture] Utilisateur standard créé : {email}, mot de passe haché (longueur) : {length}', [
            'email' => $userEmail,
            'length' => strlen($hashedPasswordUser)
        ]);

        $manager->flush();
        $this->logger->info('[UserFixture] Flush effectué. Utilisateurs persistés.');
    }
}
