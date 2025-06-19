<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PriseDeVueVoter extends Voter
{
    public const PRISEDEVUE_VIEW = 'PRISEDEVUE_VIEW';
    public const PRISEDEVUE_EDIT = 'PRISEDEVUE_EDIT';
    public const PRISEDEVUE_COMMENT = 'PRISEDEVUE_COMMENT';
    public const PRISEDEVUE_DELETE = 'PRISEDEVUE_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::PRISEDEVUE_VIEW,
            self::PRISEDEVUE_EDIT,
            self::PRISEDEVUE_COMMENT,
            self::PRISEDEVUE_DELETE
        ]) && $subject instanceof PriseDeVue;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // L'utilisateur doit être connecté
        if (!$user instanceof User) {
            return false;
        }

        // SuperAdmin et Admin ont tous les droits
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;

        // Vérifier les permissions spécifiques pour le photographe
        switch ($attribute) {
            case self::PRISEDEVUE_VIEW:
                return $this->canView($priseDeVue, $user);
            case self::PRISEDEVUE_EDIT:
                return $this->canEdit($priseDeVue, $user);
            case self::PRISEDEVUE_COMMENT:
                return $this->canComment($priseDeVue, $user);
            case self::PRISEDEVUE_DELETE:
                return $this->canDelete($priseDeVue, $user);
        }

        return false;
    }

    private function canView(PriseDeVue $priseDeVue, User $user): bool
    {
        // Un photographe peut voir ses propres prises de vue
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return $priseDeVue->getPhotographe() === $user;
        }
        
        return false;
    }

    private function canEdit(PriseDeVue $priseDeVue, User $user): bool
    {
        // Seul l'Admin peut éditer complètement une prise de vue
        return false;
    }

    private function canComment(PriseDeVue $priseDeVue, User $user): bool
    {
        // Un photographe peut modifier le commentaire de ses prises de vue
        if ($this->security->isGranted('ROLE_PHOTOGRAPHE')) {
            return $priseDeVue->getPhotographe() === $user;
        }
        
        return false;
    }

    private function canDelete(PriseDeVue $priseDeVue, User $user): bool
    {
        // Seul l'Admin peut supprimer une prise de vue
        return false;
    }
}