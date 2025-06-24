<?php

namespace App\Security\Voter;

use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use App\Security\Service\SecurityHelper;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter abstrait optimisé pour factoriser la logique commune
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Factorise 80% de la logique des Voters pour éliminer
 * les redondances et garantir la cohérence
 */
abstract class AbstractEntityVoter extends Voter
{
    public function __construct(
        protected SecurityHelper $securityHelper
    ) {}

    // === MÉTHODES ABSTRAITES - CONFIGURATION PAR VOTER CONCRET ===

    /**
     * Retourne les classes d'entités supportées par ce Voter
     */
    abstract protected function getSupportedEntities(): array;

    /**
     * Retourne les actions supportées par ce Voter
     */
    abstract protected function getSupportedActions(): array;

    /**
     * Retourne les rôles minimum requis pour chaque action
     * Format: ['ACTION' => SecurityRole::ROLE]
     */
    abstract protected function getRequiredRoles(): array;

    // === MÉTHODES TEMPLATE - LOGIQUE COMMUNE FACTORISÉE ===

    /**
     * Logique unifiée de support (Template Method)
     */
    final protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifier que l'action est supportée
        if (!$this->isActionSupported($attribute)) {
            return false;
        }

        // Vérifier que l'entité est supportée
        if (!$this->isEntitySupported($subject)) {
            return false;
        }

        return true;
    }

    /**
     * Logique unifiée de vote (Template Method)
     */
    final protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Pré-vérifications communes
        if (($preCheck = $this->preChecks($token)) !== null) {
            return $preCheck;
        }

        // Logique personnalisée du Voter concret
        $customResult = $this->customVoteLogic($attribute, $subject, $token);
        if ($customResult !== null) {
            return $customResult;
        }

        // Logique par défaut basée sur les rôles requis
        return $this->defaultVoteLogic($attribute, $subject, $token);
    }

    // === MÉTHODES COMMUNES FACTORISÉES ===

    /**
     * Pré-vérifications communes (SUPERADMIN, utilisateur anonyme)
     */
    protected function preChecks(TokenInterface $token): ?bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false; // Utilisateur anonyme → accès interdit
        }

        // ROLE_SUPERADMIN : passe-droit universel
        if ($this->securityHelper->hasMinimumRole($token, SecurityRole::SUPERADMIN)) {
            return true;
        }

        return null; // Continuer les vérifications
    }

    /**
     * Logique par défaut basée sur les rôles requis
     */
    protected function defaultVoteLogic(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $requiredRoles = $this->getRequiredRoles();
        
        if (!isset($requiredRoles[$attribute])) {
            return false; // Action non configurée
        }

        $requiredRole = $requiredRoles[$attribute];
        
        // Vérifier le rôle minimum
        if (!$this->securityHelper->hasMinimumRole($token, $requiredRole)) {
            return false;
        }

        // Vérifications d'accès à l'entité
        return $this->canAccessEntity($subject, $token);
    }

    /**
     * Vérifie si l'utilisateur peut accéder à cette entité spécifique
     */
    protected function canAccessEntity(mixed $subject, TokenInterface $token): bool
    {
        // Par défaut, si l'utilisateur a le bon rôle, il peut accéder
        // Les Voters concrets peuvent surcharger cette méthode pour une logique spécialisée
        return true;
    }

    /**
     * Vérifie si une action est supportée
     */
    protected function isActionSupported(string $attribute): bool
    {
        $supportedActions = $this->getSupportedActions();
        
        // Support des actions sous forme d'enum ou de string
        foreach ($supportedActions as $action) {
            if ($action instanceof EntityAction) {
                if ($action->value === $attribute) {
                    return true;
                }
            } elseif ($action === $attribute) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une entité est supportée
     */
    protected function isEntitySupported(mixed $subject): bool
    {
        if ($subject === null) {
            return true; // Pour les actions de création
        }

        $supportedEntities = $this->getSupportedEntities();
        
        foreach ($supportedEntities as $entityClass) {
            if ($subject instanceof $entityClass) {
                return true;
            }
        }

        return false;
    }

    // === HOOKS POUR PERSONNALISATION ===

    /**
     * Hook pour logique personnalisée du Voter concret
     * Retourne null pour continuer avec la logique par défaut
     */
    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        return null; // Par défaut, pas de logique personnalisée
    }

    // === MÉTHODES UTILITAIRES ===

    /**
     * Retourne toutes les actions CRUD basiques
     */
    protected static function getBasicCrudActions(): array
    {
        return EntityAction::getBasicActions();
    }

    /**
     * Vérifie si l'utilisateur est propriétaire de l'entité
     */
    protected function isOwner(mixed $subject, TokenInterface $token): bool
    {
        return $this->securityHelper->isEntityOwner($subject, $token->getUser());
    }

    /**
     * Vérifie si l'entité est utilisée (a des relations)
     */
    protected function isEntityUsed(mixed $subject): bool
    {
        return $this->securityHelper->isEntityUsed($subject);
    }

    /**
     * Retourne le rôle le plus élevé de l'utilisateur
     */
    protected function getHighestUserRole(TokenInterface $token): ?SecurityRole
    {
        return $this->securityHelper->getHighestRole($token);
    }

    /**
     * Formate un message d'erreur
     */
    protected function formatError(string $action, mixed $subject): string
    {
        $entityType = $subject ? get_class($subject) : 'Entity';
        return $this->securityHelper->formatSecurityError($action, $entityType);
    }
}
