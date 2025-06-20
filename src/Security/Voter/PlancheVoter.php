<?php

namespace App\Security\Voter;

use App\Entity\Planche;
use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class PlancheVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::actions(), true)
            && ($subject instanceof Planche || $attribute === EntityAction::CREATE->value);
    }

    protected function voteOnAttribute(string $attribute, mixed $planche, TokenInterface $token): bool
    {
        if (($pre = $this->preChecks($token)) !== null) {
            return $pre;
        }

        /* ---- ADMIN ---- */
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // Règle métier : suppression interdite si planche utilisée
            if ($attribute === EntityAction::DELETE->value && !$this->canDelete($planche)) {
                return false;
            }

            return true; // autorisé pour VIEW, EDIT, CREATE, etc.
        }

        /* ---- PHOTOGRAPHE ---- */
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return $attribute === EntityAction::VIEW->value;
        }

        return false; // défaut
    }

    private function canDelete(Planche $planche): bool
    {
        return $planche->getPrisesDeVue()->isEmpty();
    }
}
