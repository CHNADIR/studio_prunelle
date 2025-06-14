<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security; // Importer Security
use Symfony\Component\Security\Core\User\UserInterface;

class EcoleVoter extends Voter
{
    public const VIEW = 'ECOLE_VIEW';
    public const EDIT = 'ECOLE_EDIT'; // Généralement pour ROLE_ADMIN
    public const DELETE = 'ECOLE_DELETE'; // Généralement pour ROLE_ADMIN
    public const CREATE = 'ECOLE_CREATE'; // Généralement pour ROLE_ADMIN

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

        // Pour CREATE, le sujet peut être null ou la classe Ecole elle-même.
        // Le Voter supporte l'attribut CREATE; la logique fine (si le sujet est valide pour CREATE)
        // sera dans voteOnAttribute si nécessaire, ou on assume que le contrôleur passe null/Classe::class.
        if ($attribute === self::CREATE) {
            return true;
        }

        // Pour VIEW, EDIT, DELETE, le sujet doit être une instance de Ecole.
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

        // ROLE_ADMIN peut tout faire sur les écoles
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Pour les utilisateurs non-admin (ROLE_USER)
        /** @var Ecole|null $ecole */
        $ecole = $subject; // $subject sera une Ecole pour VIEW, EDIT, DELETE

        switch ($attribute) {
            case self::VIEW:
                // Si supports() garantit que $subject est une Ecole pour VIEW,
                // et que ROLE_USER peut voir toutes les écoles (selon access_control).
                return true;
            case self::EDIT:
            case self::DELETE:
                // ROLE_USER ne peut pas modifier ou supprimer les écoles selon la configuration actuelle de security.yaml
                return false;
            case self::CREATE:
                // ROLE_USER ne peut pas créer d'écoles selon la configuration actuelle de security.yaml
                return false;
        }

        return false;
    }
}
