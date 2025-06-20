<?php

namespace App\Security\Voter;

use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\Theme;
use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * ADMIN & SUPERADMIN : CRUD
 * Autres rôles        : aucun accès
 */
final class ReferentielVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::actions(), true)
            && ($subject instanceof TypePrise
                || $subject instanceof TypeVente
                || $subject instanceof Theme);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (($pre = $this->preChecks($token)) !== null) {
            return $pre;
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
