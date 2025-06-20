<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
/**
 * ADMIN  : CRUD complet
 * PHOTO  : VIEW & EDIT (commentaire) sur ses propres prises
 */
final class PriseDeVueVoter extends AbstractEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::actions(), true)
            && $subject instanceof PriseDeVue;
    }

    protected function voteOnAttribute(string $attribute, mixed $pdv, TokenInterface $token): bool
    {
        if (($pre = $this->preChecks($token)) !== null) {
            return $pre;
        }

        /* ---- ADMIN ---- */
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;                               // CRUD
        }

        /* ---- PHOTOGRAPHE ---- */
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            $isOwner = $pdv->getPhotographe()?->getId() === $token->getUser()->getId();

            return match ($attribute) {
                EntityAction::VIEW->value  => $isOwner,
                EntityAction::EDIT->value  => $isOwner,
                default                    => false,
            };
        }

        return false;
    }
}
