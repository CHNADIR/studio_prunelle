<?php

namespace App\Security\Voter;

use App\Entity\Ecole;
use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter pour les écoles - Version optimisée
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Utilise AbstractEntityVoter pour factoriser la logique commune
 * Règles: ADMIN & SUPERADMIN = CRUD complet, PHOTOGRAPHE = aucun accès
 */
final class EcoleVoter extends AbstractEntityVoter
{
    // === CONFIGURATION DU VOTER ===

    protected function getSupportedEntities(): array
    {
        return [Ecole::class];
    }

    protected function getSupportedActions(): array
    {
        return [
            EntityAction::VIEW,
            EntityAction::CREATE,
            EntityAction::EDIT,
            EntityAction::DELETE,
            EntityAction::ECOLE_VIEW,
            EntityAction::ECOLE_CREATE,
            EntityAction::ECOLE_EDIT,
            EntityAction::ECOLE_DELETE,
        ];
    }

    protected function getRequiredRoles(): array
    {
        return [
            EntityAction::VIEW->value => SecurityRole::ADMIN,
            EntityAction::CREATE->value => SecurityRole::ADMIN,
            EntityAction::EDIT->value => SecurityRole::ADMIN,
            EntityAction::DELETE->value => SecurityRole::ADMIN,
            EntityAction::ECOLE_VIEW->value => SecurityRole::ADMIN,
            EntityAction::ECOLE_CREATE->value => SecurityRole::ADMIN,
            EntityAction::ECOLE_EDIT->value => SecurityRole::ADMIN,
            EntityAction::ECOLE_DELETE->value => SecurityRole::ADMIN,
        ];
    }

    // === LOGIQUE SPÉCIALISÉE (SI NÉCESSAIRE) ===

    /**
     * Vérifications spécifiques aux écoles
     */
    protected function canAccessEntity(mixed $subject, TokenInterface $token): bool
    {
        // Pour les écoles, pas de logique propriétaire
        // Seul le rôle compte (déjà vérifié dans defaultVoteLogic)
        return true;
    }

    /**
     * Logique personnalisée pour les écoles
     */
    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        // Vérification de suppression : ne pas supprimer si l'école a des prises de vue
        if ($attribute === EntityAction::DELETE->value || $attribute === EntityAction::ECOLE_DELETE->value) {
            if ($this->isEntityUsed($subject)) {
                return false; // École utilisée, suppression interdite
            }
        }

        return null; // Continuer avec la logique par défaut
    }
}
