<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures pour les utilisateurs - Système complet
 * Pattern appliqué: Service Layer + Factory Pattern
 * Responsabilité: Création d'utilisateurs de test pour tous les rôles
 */
class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadSuperAdmins($manager);
        $this->loadAdmins($manager);
        $this->loadPhotographes($manager);
        $this->loadUtilisateursTest($manager);
        
        $manager->flush();
    }

    /**
     * Charge les super administrateurs
     */
    private function loadSuperAdmins(ObjectManager $manager): void
    {
        $superAdmins = [
            [
                'nom' => 'Studio Prunelle',
                'username' => 'superadmin',
                'email' => 'admin@studio-prunelle.fr',
                'password' => 'SuperAdmin123!',
                'roles' => ['ROLE_SUPERADMIN']
            ],
            [
                'nom' => 'Directeur Technique',
                'username' => 'tech',
                'email' => 'admin@studioprunelle.fr',
                'password' => 'password',
                'roles' => ['ROLE_SUPERADMIN']
            ]
        ];

        foreach ($superAdmins as $index => $userData) {
            $user = $this->createUser($userData);
            $manager->persist($user);
            $this->addReference('superadmin_' . $index, $user);
        }
    }

    /**
     * Charge les administrateurs
     */
    private function loadAdmins(ObjectManager $manager): void
    {
        $admins = [
            [
                'nom' => 'Responsable Commercial',
                'username' => 'commercial',
                'email' => 'commercial@studio-prunelle.fr',
                'password' => 'Admin123!',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'nom' => 'Gestionnaire Écoles',
                'username' => 'gestionnaire',
                'email' => 'gestionnaire@studio-prunelle.fr',
                'password' => 'Gestionnaire123!',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'nom' => 'Assistant Direction',
                'username' => 'assistant',
                'email' => 'assistant@studio-prunelle.fr',
                'password' => 'Assistant123!',
                'roles' => ['ROLE_ADMIN']
            ]
        ];

        foreach ($admins as $index => $userData) {
            $user = $this->createUser($userData);
            $manager->persist($user);
            $this->addReference('admin_' . $index, $user);
        }
    }

    /**
     * Charge les photographes
     */
    private function loadPhotographes(ObjectManager $manager): void
    {
        $photographes = [
            [
                'nom' => 'Marie Durand',
                'username' => 'marie.durand',
                'email' => 'marie.durand@studio-prunelle.fr',
                'password' => 'Photographe123!',
                'roles' => ['ROLE_PHOTOGRAPHE']
            ],
            [
                'nom' => 'Pierre Martin',
                'username' => 'pierre.martin',
                'email' => 'pierre.martin@studio-prunelle.fr',
                'password' => 'Photo123!',
                'roles' => ['ROLE_PHOTOGRAPHE']
            ],
            [
                'nom' => 'Sophie Bernard',
                'username' => 'sophie.bernard',
                'email' => 'sophie.bernard@studio-prunelle.fr',
                'password' => 'Sophie123!',
                'roles' => ['ROLE_PHOTOGRAPHE']
            ],
            [
                'nom' => 'Lucas Moreau',
                'username' => 'lucas.moreau',
                'email' => 'lucas.moreau@studio-prunelle.fr',
                'password' => 'Lucas123!',
                'roles' => ['ROLE_PHOTOGRAPHE']
            ],
            [
                'nom' => 'Emma Rousseau',
                'username' => 'emma.rousseau',
                'email' => 'emma.rousseau@studio-prunelle.fr',
                'password' => 'Emma123!',
                'roles' => ['ROLE_PHOTOGRAPHE']
            ]
        ];

        foreach ($photographes as $index => $userData) {
            $user = $this->createUser($userData);
            $manager->persist($user);
            $this->addReference('photographe_' . $userData['username'], $user);
        }
    }

    /**
     * Charge des utilisateurs de test
     */
    private function loadUtilisateursTest(ObjectManager $manager): void
    {
        $utilisateursTest = [
            [
                'nom' => 'Utilisateur Test',
                'username' => 'test',
                'email' => 'test@studio-prunelle.fr',
                'password' => 'Test123!',
                'roles' => ['ROLE_USER']
            ],
            [
                'nom' => 'Demo User',
                'username' => 'demo',
                'email' => 'demo@studio-prunelle.fr',
                'password' => 'Demo123!',
                'roles' => ['ROLE_USER']
            ]
        ];

        foreach ($utilisateursTest as $index => $userData) {
            $user = $this->createUser($userData);
            $manager->persist($user);
            $this->addReference('user_test_' . $index, $user);
        }
    }

    /**
     * Factory method pour créer un utilisateur
     * Pattern: Factory Method
     */
    private function createUser(array $userData): User
    {
        $user = new User();
        $user->setNom($userData['nom']);
        $user->setUsername($userData['username']);
        $user->setEmail($userData['email']);
        $user->setRoles($userData['roles']);
        
        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $userData['password']
        );
        $user->setPassword($hashedPassword);
        
        // Définir la dernière connexion pour simuler des utilisateurs actifs
        if (in_array('ROLE_PHOTOGRAPHE', $userData['roles'])) {
            $user->setLastLogin(new \DateTime('-' . rand(1, 30) . ' days'));
        } elseif (in_array('ROLE_ADMIN', $userData['roles'])) {
            $user->setLastLogin(new \DateTime('-' . rand(1, 7) . ' days'));
        } elseif (in_array('ROLE_SUPERADMIN', $userData['roles'])) {
            $user->setLastLogin(new \DateTime('-' . rand(1, 3) . ' days'));
        }
        
        return $user;
    }

    /**
     * Groupe de cette fixture
     */
    public static function getGroups(): array
    {
        return ['user', 'security', 'core'];
    }

    /**
     * Pas de dépendances - les utilisateurs sont créés en premier
     */
    public function getDependencies(): array
    {
        return [];
    }
} 