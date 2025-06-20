<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * SUPERADMIN : CRUD complet
 * ADMIN      : VIEW seulement
 * Utilisateur sur lui-même : EDIT
 */
final class UserVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::actions(), true)
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $targetUser, TokenInterface $token): bool
    {
        if (($pre = $this->preChecks($token)) !== null) {
            return $pre;               // SUPERADMIN autorisé
        }

        /* ---- ADMIN ---- */
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $attribute === EntityAction::VIEW->value;
        }

        /* ---- Self-service ---- */
        $currentUser = $token->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $targetUser->getId()) {
            return $attribute === EntityAction::EDIT->value;
        }

        return false;
    }
}
