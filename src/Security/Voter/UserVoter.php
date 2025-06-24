<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter pour les utilisateurs - Version optimisée
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Règles:
 * - SUPERADMIN: CRUD complet
 * - ADMIN: VIEW seulement  
 * - Utilisateur: EDIT sur lui-même
 */
final class UserVoter extends AbstractEntityVoter
{
    // === CONFIGURATION DU VOTER ===

    protected function getSupportedEntities(): array
    {
        return [User::class];
    }

    protected function getSupportedActions(): array
    {
        return [
            EntityAction::VIEW,
            EntityAction::CREATE,
            EntityAction::EDIT,
            EntityAction::DELETE,
            EntityAction::USER_MANAGE_ROLES,
            EntityAction::USER_RESET_PASSWORD,
        ];
    }

    protected function getRequiredRoles(): array
    {
        return [
            EntityAction::VIEW->value => SecurityRole::ADMIN,
            EntityAction::CREATE->value => SecurityRole::SUPERADMIN,
            EntityAction::EDIT->value => SecurityRole::USER, // Self-service autorisé
            EntityAction::DELETE->value => SecurityRole::SUPERADMIN,
            EntityAction::USER_MANAGE_ROLES->value => SecurityRole::SUPERADMIN,
            EntityAction::USER_RESET_PASSWORD->value => SecurityRole::SUPERADMIN,
        ];
    }

    // === LOGIQUE SPÉCIALISÉE POUR LES UTILISATEURS ===

    /**
     * Logique personnalisée pour les utilisateurs
     */
    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        $currentUser = $token->getUser();
        
        // Logique self-service pour EDIT
        if ($attribute === EntityAction::EDIT->value) {
            // L'utilisateur peut modifier son propre profil
            if ($this->isOwner($subject, $token)) {
                return true;
            }
            
            // Les Admin peuvent modifier les autres utilisateurs
            if ($this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN)) {
                return true;
            }
            
            return false;
        }

        // Logique spéciale pour VIEW
        if ($attribute === EntityAction::VIEW->value) {
            // L'utilisateur peut voir son propre profil
            if ($this->isOwner($subject, $token)) {
                return true;
            }
            
            // Les Admin peuvent voir les autres utilisateurs
            if ($this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN)) {
                return true;
            }
            
            return false;
        }

        return null; // Continuer avec la logique par défaut
    }

    /**
     * Vérifications d'accès spécifiques aux utilisateurs
     */
    protected function canAccessEntity(mixed $subject, TokenInterface $token): bool
    {
        // Vérifier qu'on ne peut pas supprimer son propre compte
        $actionIsDelete = in_array($token->getAttribute('_security_last_action') ?? '', [
            EntityAction::DELETE->value,
        ], true);
        
        if ($actionIsDelete && $this->isOwner($subject, $token)) {
            return false; // Ne peut pas supprimer son propre compte
        }

        return true;
    }
}
