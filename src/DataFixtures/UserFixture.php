<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;

    public const ADMIN_USER_REFERENCE = 'user-admin';
    public const RESP_ADMIN_USER_REFERENCE = 'user-resp-admin';
    public const PHOTOGRAPHE1_USER_REFERENCE = 'user-photographe1';
    public const PHOTOGRAPHE2_USER_REFERENCE = 'user-photographe2';
    public const SIMPLE_USER_REFERENCE = 'user-simple';


    public function __construct(UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger)
    {
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->logger->info('[UserFixture] Début du chargement des utilisateurs.');

        $adminTech = new User();
        $adminTechEmail = 'admin@studioprunelle.fr';
        $adminTech->setEmail($adminTechEmail);
        $adminTech->setRoles([User::ROLE_ADMIN]);
        $adminTech->setPassword($this->passwordHasher->hashPassword($adminTech, 'password'));
        $manager->persist($adminTech);
        $this->addReference(self::ADMIN_USER_REFERENCE, $adminTech);
        $this->logger->info('[UserFixture] Utilisateur ROLE_ADMIN créé : {email}', ['email' => $adminTechEmail]);

        $respAdmin = new User();
        $respAdminEmail = 'resp_admin@studioprunelle.fr';
        $respAdmin->setEmail($respAdminEmail);
        $respAdmin->setRoles([User::ROLE_RESPONSABLE_ADMINISTRATIF]);
        $respAdmin->setPassword($this->passwordHasher->hashPassword($respAdmin, 'password'));
        $manager->persist($respAdmin);
        $this->addReference(self::RESP_ADMIN_USER_REFERENCE, $respAdmin);
        $this->logger->info('[UserFixture] Utilisateur ROLE_RESPONSABLE_ADMINISTRATIF créé : {email}', ['email' => $respAdminEmail]);

        $photographe1 = new User();
        $photographe1Email = 'photographe1@studioprunelle.fr';
        $photographe1->setEmail($photographe1Email);
        $photographe1->setRoles([User::ROLE_PHOTOGRAPHE]);
        $photographe1->setPassword($this->passwordHasher->hashPassword($photographe1, 'password'));
        $manager->persist($photographe1);
        $this->addReference(self::PHOTOGRAPHE1_USER_REFERENCE, $photographe1);
        $this->logger->info('[UserFixture] Utilisateur ROLE_PHOTOGRAPHE créé : {email}', ['email' => $photographe1Email]);

        $photographe2 = new User();
        $photographe2Email = 'photographe2@studioprunelle.fr';
        $photographe2->setEmail($photographe2Email);
        $photographe2->setRoles([User::ROLE_PHOTOGRAPHE]);
        $photographe2->setPassword($this->passwordHasher->hashPassword($photographe2, 'password'));
        $manager->persist($photographe2);
        $this->addReference(self::PHOTOGRAPHE2_USER_REFERENCE, $photographe2);
        $this->logger->info('[UserFixture] Utilisateur ROLE_PHOTOGRAPHE créé : {email}', ['email' => $photographe2Email]);
        
        $simpleUser = new User();
        $simpleUserEmail = 'user@studioprunelle.fr';
        $simpleUser->setEmail($simpleUserEmail);
        // $simpleUser->setRoles([User::ROLE_USER]); // Automatiquement ajouté
        $simpleUser->setPassword($this->passwordHasher->hashPassword($simpleUser, 'password'));
        $manager->persist($simpleUser);
        $this->addReference(self::SIMPLE_USER_REFERENCE, $simpleUser);
        $this->logger->info('[UserFixture] Utilisateur ROLE_USER (simple) créé : {email}', ['email' => $simpleUserEmail]);

        $manager->flush();
        $this->logger->info('[UserFixture] Flush effectué. Tous les utilisateurs de test sont persistés.');
    }
}
