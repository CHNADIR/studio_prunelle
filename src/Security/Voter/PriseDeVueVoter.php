<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PriseDeVueVoter extends Voter
{
    public const VIEW = 'PRISE_DE_VUE_VIEW';
    public const EDIT = 'PRISE_DE_VUE_EDIT';
    public const EDIT_COMMENT = 'PRISE_DE_VUE_EDIT_COMMENT';
    public const DELETE = 'PRISE_DE_VUE_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::EDIT_COMMENT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof PriseDeVue) {
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

        // CORRECTION: utiliser des chaînes de caractères directes
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;

        if ($this->security->isGranted('ROLE_RESPONSABLE_ADMINISTRATIF')) {
            switch ($attribute) {
                case self::VIEW:
                case self::EDIT:
                case self::DELETE:
                    return true;
            }
        }

        if ($this->security->isGranted('ROLE_PHOTOGRAPHE') && $user instanceof User) {
            switch ($attribute) {
                case self::VIEW:
                    return $priseDeVue->getPhotographe() === $user->getEmail();
                case self::EDIT_COMMENT:
                    return $priseDeVue->getPhotographe() === $user->getEmail();
                case self::EDIT:
                case self::DELETE:
                    return false;
            }
        }
        return false;
    }
}
