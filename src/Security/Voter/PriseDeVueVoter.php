<?php

namespace App\Security\Voter;

use App\Entity\PriseDeVue;
use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter pour les prises de vue - Version optimisée
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Règles:
 * - ADMIN: CRUD complet
 * - PHOTOGRAPHE: VIEW & EDIT (commentaire) sur ses propres prises
 */
final class PriseDeVueVoter extends AbstractEntityVoter
{
    // === CONFIGURATION DU VOTER ===

    protected function getSupportedEntities(): array
    {
        return [PriseDeVue::class];
    }

    protected function getSupportedActions(): array
    {
        return [
            EntityAction::VIEW,
            EntityAction::CREATE,
            EntityAction::EDIT,
            EntityAction::DELETE,
            EntityAction::PRISEDEVUE_VIEW,
            EntityAction::PRISEDEVUE_EDIT,
            EntityAction::PRISEDEVUE_DELETE,
            EntityAction::PRISEDEVUE_COMMENT,
        ];
    }

    protected function getRequiredRoles(): array
    {
        return [
            EntityAction::VIEW->value => SecurityRole::PHOTOGRAPHE, // Photographe peut voir ses prises
            EntityAction::CREATE->value => SecurityRole::PHOTOGRAPHE,
            EntityAction::EDIT->value => SecurityRole::PHOTOGRAPHE, // Photographe peut modifier ses prises
            EntityAction::DELETE->value => SecurityRole::ADMIN, // Seuls les Admin peuvent supprimer
            EntityAction::PRISEDEVUE_VIEW->value => SecurityRole::PHOTOGRAPHE,
            EntityAction::PRISEDEVUE_EDIT->value => SecurityRole::PHOTOGRAPHE,
            EntityAction::PRISEDEVUE_DELETE->value => SecurityRole::ADMIN,
            EntityAction::PRISEDEVUE_COMMENT->value => SecurityRole::PHOTOGRAPHE,
        ];
    }

    // === LOGIQUE SPÉCIALISÉE POUR LES PRISES DE VUE ===

    /**
     * Logique personnalisée pour les prises de vue
     */
    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        // Les Admin ont accès complet
        if ($this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return true;
        }

        // Pour les Photographes, vérifier la propriété
        if ($this->securityHelper->hasMinimumRole($token, SecurityRole::PHOTOGRAPHE)) {
            return $this->handlePhotographeAccess($attribute, $subject, $token);
        }

        return false; // Autres rôles n'ont pas accès
    }

    /**
     * Gère l'accès des photographes à leurs prises de vue
     */
    private function handlePhotographeAccess(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Vérifier que le photographe est propriétaire
        if (!$this->isOwner($subject, $token)) {
            return false;
        }

        // Définir les actions autorisées pour les photographes sur leurs prises de vue
        $allowedActions = [
            EntityAction::VIEW->value,
            EntityAction::EDIT->value, // Pour modifier des détails ou commentaires
            EntityAction::PRISEDEVUE_VIEW->value,
            EntityAction::PRISEDEVUE_EDIT->value,
            EntityAction::PRISEDEVUE_COMMENT->value,
        ];

        return in_array($attribute, $allowedActions, true);
    }

    /**
     * Vérifications d'accès spécifiques aux prises de vue
     */
    protected function canAccessEntity(mixed $subject, TokenInterface $token): bool
    {
        // Admin peut accéder à toutes les prises de vue
        if ($this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return true;
        }

        // Photographe ne peut accéder qu'à ses propres prises de vue
        if ($this->securityHelper->hasMinimumRole($token, SecurityRole::PHOTOGRAPHE)) {
            return $this->isOwner($subject, $token);
        }

        return false;
    }
}
