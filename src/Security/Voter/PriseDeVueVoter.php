<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PriseDeVueVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const EDIT_COMMENT = 'edit_comment';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::EDIT_COMMENT])
            && $subject instanceof PriseDeVue;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return false;
        }

        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;

        // Les admins peuvent tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Les photographes ne peuvent voir/modifier que leurs prises de vue
        switch ($attribute) {
            case self::VIEW:
                return $this->canView($priseDeVue, $user);
            case self::EDIT:
                return $this->canEdit($priseDeVue, $user);
            case self::DELETE:
                return $this->canDelete($priseDeVue, $user);
            case self::EDIT_COMMENT:
                return $this->canEditComment($priseDeVue, $user);
        }

        return false;
    }

    private function canView(PriseDeVue $priseDeVue, User $user): bool
    {
        // Un photographe peut voir ses prises de vue
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return $priseDeVue->getPhotographe() === $user;
        }

        return false;
    }

    private function canEdit(PriseDeVue $priseDeVue, User $user): bool
    {
        // Seuls les admins peuvent modifier complètement les prises de vue
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(PriseDeVue $priseDeVue, User $user): bool
    {
        // Seuls les admins peuvent supprimer des prises de vue
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEditComment(PriseDeVue $priseDeVue, User $user): bool
    {
        // Un photographe peut modifier le commentaire de ses prises de vue
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return $priseDeVue->getPhotographe() === $user;
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}