<?php

namespace App\Security\Voter;

use App\Security\Attribute\EntityAction;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractEntityVoter extends Voter
{
    public function __construct(protected Security $security) {}

    /** Pré-filtrage des rôles “globaux” */
    protected function preChecks(TokenInterface $token): ?bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false; // utilisateur anonyme → accès interdit
        }

        // ROLE_SUPERADMIN : passe-droit universel
        if ($this->security->isGranted('ROLE_SUPERADMIN')) {
            return true;
        }

        // Vous pouvez ajouter ici un “ROLE_RESPONSABLE_ADMINISTRATIF” si nécessaire

        return null; // on laisse le voter concret décider
    }

    /** Petit raccourci pour récupérer toutes les actions possibles */
    protected static function actions(): array
    {
        return array_column(EntityAction::cases(), 'value');
    }
}
