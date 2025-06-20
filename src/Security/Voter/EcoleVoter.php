<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ADMIN & SUPERADMIN : CRUD
 * PHOTOGRAPHE        : aucun accès
 */
final class EcoleVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::actions(), true)
            && $subject instanceof Ecole;
    }

    protected function voteOnAttribute(string $attribute, mixed $ecole, TokenInterface $token): bool
    {
        if (($pre = $this->preChecks($token)) !== null) {
            return $pre;                          // SUPERADMIN autorisé
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
