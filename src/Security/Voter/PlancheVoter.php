<?php

class PlancheVoter extends Voter
{
    public const DELETE = 'PLANche_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof Planche;
    }

    protected function voteOnAttribute(string $attribute, mixed $planche, TokenInterface $token): bool
    {
        // interdiction si la planche est déjà liée à une prise de vue
        return $planche->getPrisesDeVue()->isEmpty();
    }
}
