<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EcoleVoter extends Voter
{
    public const VIEW = 'ECOLE_VIEW';
    public const EDIT = 'ECOLE_EDIT';
    public const DELETE = 'ECOLE_DELETE';
    public const CREATE = 'ECOLE_CREATE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE])) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return true;
        }

        if (!$subject instanceof Ecole) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_RESPONSABLE_ADMINISTRATIF')) {
            if ($attribute === self::CREATE) return true;

            if ($subject instanceof Ecole) {
                 switch ($attribute) {
                    case self::VIEW:
                    case self::EDIT:
                    case self::DELETE:
                        return true;
                }
            }
        }

        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            if ($attribute === self::VIEW && $subject instanceof Ecole) {
                return true;
            }
        }

        return false;
    }
}
