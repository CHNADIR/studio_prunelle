<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserSecurityHelper
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * Vérifie si l'utilisateur donné est le dernier SuperAdmin
     */
    public function isLastSuperAdmin(User $user): bool
    {
        $superAdmins = $this->userRepository->findByRole('ROLE_SUPERADMIN');
        // On compte le nombre de superadmins actifs
        $count = count($superAdmins);
        return $count === 1 && $superAdmins[0]->getId() === $user->getId();
    }
}