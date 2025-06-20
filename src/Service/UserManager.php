<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    public function save(User $user, ?string $plainPassword = null): void
    {
        if ($plainPassword) {
            $user->setPassword(
                $this->hasher->hashPassword($user, $plainPassword)
            );
        }
        $this->em->persist($user);
        $this->em->flush();
    }
}