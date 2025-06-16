<?php

namespace App\Security\Voter;

use App\Entity\Planche;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PlancheVoter extends Voter
{
    public const VIEW = 'PLANCHE_VIEW';
    public const EDIT = 'PLANCHE_EDIT';
    public const DELETE = 'PLANCHE_DELETE';
    public const CREATE = 'PLANCHE_CREATE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE])
            && ($subject instanceof Planche || $attribute === self::CREATE);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Les administrateurs peuvent tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Les responsables administratifs peuvent tout faire
        if ($this->security->isGranted('ROLE_RESPONSABLE_ADMINISTRATIF')) {
            return true;
        }

        // Les photographes peuvent uniquement voir les planches
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            if ($attribute === self::VIEW) {
                return true;
            }
        }

        return false;
    }
}