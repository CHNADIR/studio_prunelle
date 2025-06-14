<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security; // Importer Security
use Symfony\Component\Security\Core\User\UserInterface;

class PriseDeVueVoter extends Voter
{
    public const VIEW = 'PRISE_DE_VUE_VIEW';
    public const EDIT = 'PRISE_DE_VUE_EDIT';
    public const DELETE = 'PRISE_DE_VUE_DELETE';
    // public const CREATE = 'PRISE_DE_VUE_CREATE'; // La création est souvent gérée au niveau du contrôleur/route

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Si l'attribut n'est pas l'un de ceux que nous supportons, retourne false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // Vote uniquement sur les objets PriseDeVue
        if (!$subject instanceof PriseDeVue) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas authentifié, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ROLE_ADMIN peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var PriseDeVue $priseDeVue */
        $priseDeVue = $subject;

        // Vérification spécifique pour ROLE_USER
        if ($user instanceof User) { // Assurez-vous que $user est bien votre entité App\Entity\User
            switch ($attribute) {
                case self::VIEW:
                    // L'utilisateur peut voir la prise de vue si son email correspond au champ photographe
                    return $priseDeVue->getPhotographe() === $user->getEmail();
                case self::EDIT:
                    // L'utilisateur peut modifier la prise de vue si son email correspond au champ photographe
                    return $priseDeVue->getPhotographe() === $user->getEmail();
                case self::DELETE:
                    // L'utilisateur peut supprimer la prise de vue si son email correspond au champ photographe
                    return $priseDeVue->getPhotographe() === $user->getEmail();
            }
        }

        return false;
    }
}
