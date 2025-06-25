<?php

namespace App\Security\Service;

use App\Security\Attribute\SecurityRole;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Service utilitaire pour la sécurité
 * Pattern appliqué: Service Layer Pattern (patterns.md)
 * 
 * Centralise les méthodes utilitaires de sécurité pour éviter
 * les duplications dans les Voters et garantir la cohérence
 */
class SecurityHelper
{
    public function __construct(
        private Security $security
    ) {}
    
    /**
     * Vérifie si l'utilisateur a un rôle minimum requis
     */
    public function hasMinimumRole(TokenInterface $token, SecurityRole $requiredRole): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // Vérification directe du rôle
        if ($this->security->isGranted($requiredRole->value)) {
            return true;
        }
        
        // Vérification par hiérarchie
        foreach ($user->getRoles() as $userRole) {
            $securityRole = SecurityRole::fromString($userRole);
            if ($securityRole && $securityRole->hasAccess($requiredRole)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vérifie si l'utilisateur est propriétaire d'une entité
     */
    public function isEntityOwner(mixed $entity, UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }
        
        // Vérification pour PriseDeVue
        if (method_exists($entity, 'getPhotographe')) {
            $photographe = $entity->getPhotographe();
            return $photographe && $photographe->getId() === $user->getId();
        }
        
        // Vérification pour User (lui-même)
        if ($entity instanceof User) {
            return $entity->getId() === $user->getId();
        }
        
        // Vérification générique par propriété 'user'
        if (method_exists($entity, 'getUser')) {
            $entityUser = $entity->getUser();
            return $entityUser && $entityUser->getId() === $user->getId();
        }
        
        return false;
    }
    
    /**
     * Vérifie si l'utilisateur peut accéder aux référentiels
     */
    public function canAccessReferential(TokenInterface $token): bool
    {
        return $this->hasMinimumRole($token, SecurityRole::ADMIN);
    }
    
    /**
     * Vérifie si l'utilisateur peut gérer les utilisateurs
     */
    public function canManageUsers(TokenInterface $token): bool
    {
        return $this->hasMinimumRole($token, SecurityRole::SUPERADMIN);
    }
    
    /**
     * Vérifie si l'utilisateur peut supprimer des valeurs d'enum
     */
    public function canDeleteEnumValues(TokenInterface $token): bool
    {
        return $this->hasMinimumRole($token, SecurityRole::SUPERADMIN);
    }
    
    /**
     * Retourne le rôle le plus élevé de l'utilisateur
     */
    public function getHighestRole(TokenInterface $token): ?SecurityRole
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return null;
        }
        
        $highestRole = null;
        $highestPriority = 0;
        
        foreach ($user->getRoles() as $roleString) {
            $role = SecurityRole::fromString($roleString);
            if ($role && $role->getPriority() > $highestPriority) {
                $highestRole = $role;
                $highestPriority = $role->getPriority();
            }
        }
        
        return $highestRole;
    }
    
    /**
     * Vérifie si une entité est utilisée (a des relations)
     */
    public function isEntityUsed(mixed $entity): bool
    {
        if (!$entity) {
            return false;
        }
        
        // Vérification pour les référentiels
        if (method_exists($entity, 'getPrisesDeVue')) {
            $prisesDeVue = $entity->getPrisesDeVue();
            
            // Si c'est une collection, vérifier qu'elle est vide
            if ($prisesDeVue instanceof \Doctrine\Common\Collections\Collection) {
                return !$prisesDeVue->isEmpty();
            }
            
            return $prisesDeVue !== null;
        }
        
        return false;
    }
    
    /**
     * Vérifie si l'utilisateur peut effectuer une action sur une entité
     */
    public function canPerformAction(
        TokenInterface $token, 
        string $action, 
        mixed $entity = null
    ): bool {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // Super admin peut tout faire
        if ($this->hasMinimumRole($token, SecurityRole::SUPERADMIN)) {
            return true;
        }
        
        // Logique spécifique selon le type d'action
        return match ($action) {
            'VIEW' => $this->canView($token, $entity),
            'CREATE' => $this->canCreate($token, $entity),
            'EDIT' => $this->canEdit($token, $entity),
            'DELETE' => $this->canDelete($token, $entity),
            default => false,
        };
    }
    
    /**
     * Vérifie les permissions de lecture
     */
    private function canView(TokenInterface $token, mixed $entity): bool
    {
        // Admin peut voir tout
        if ($this->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return true;
        }
        
        // Photographe peut voir ses propres entités
        if ($this->hasMinimumRole($token, SecurityRole::PHOTOGRAPHE)) {
            return $this->isEntityOwner($entity, $token->getUser());
        }
        
        return false;
    }
    
    /**
     * Vérifie les permissions de création
     */
    private function canCreate(TokenInterface $token, mixed $entity): bool
    {
        return $this->hasMinimumRole($token, SecurityRole::ADMIN);
    }
    
    /**
     * Vérifie les permissions de modification
     */
    private function canEdit(TokenInterface $token, mixed $entity): bool
    {
        // Admin peut modifier tout
        if ($this->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return true;
        }
        
        // Photographe peut modifier ses propres entités
        if ($this->hasMinimumRole($token, SecurityRole::PHOTOGRAPHE)) {
            return $this->isEntityOwner($entity, $token->getUser());
        }
        
        return false;
    }
    
    /**
     * Vérifie les permissions de suppression
     */
    private function canDelete(TokenInterface $token, mixed $entity): bool
    {
        // Seuls les admin peuvent supprimer
        if (!$this->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return false;
        }
        
        // Ne pas supprimer les entités utilisées
        if ($this->isEntityUsed($entity)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Formate un message d'erreur de sécurité
     */
    public function formatSecurityError(string $action, string $entityType): string
    {
        return sprintf(
            'Accès refusé : vous n\'avez pas les droits pour %s %s',
            $this->getActionDisplayName($action),
            $this->getEntityDisplayName($entityType)
        );
    }
    
    /**
     * Retourne le nom affiché d'une action
     */
    private function getActionDisplayName(string $action): string
    {
        return match (strtoupper($action)) {
            'VIEW' => 'consulter',
            'CREATE' => 'créer',
            'EDIT' => 'modifier',
            'DELETE' => 'supprimer',
            default => 'effectuer cette action sur',
        };
    }
    
    /**
     * Retourne le nom affiché d'un type d'entité
     */
    private function getEntityDisplayName(string $entityType): string
    {
        return match ($entityType) {
            'PriseDeVue' => 'cette prise de vue',
            'User' => 'cet utilisateur',
            'Ecole' => 'cette école',
            'TypePrise' => 'ce type de prise',
            'TypeVente' => 'ce type de vente',
            'Theme' => 'ce thème',
            'Planche' => 'cette planche',
            'PochetteIndiv' => 'cette pochette individuelle',
            'PochetteFratrie' => 'cette pochette fratrie',
            default => 'cette entité',
        };
    }
} 