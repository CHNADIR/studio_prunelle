<?php

namespace App\Security\Attribute;

/**
 * Enum pour les rôles de sécurité avec méthodes utilitaires
 * Pattern appliqué: Enum Pattern (patterns.md)
 * 
 * Centralise la logique des rôles pour éviter les duplications
 * et garantir la cohérence dans les Voters
 */
enum SecurityRole: string
{
    case SUPERADMIN = 'ROLE_SUPERADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case PHOTOGRAPHE = 'ROLE_PHOTOGRAPHE';
    case USER = 'ROLE_USER';
    
    /**
     * Retourne la hiérarchie des rôles
     */
    public static function getHierarchy(): array
    {
        return [
            self::SUPERADMIN->value => [
                self::ADMIN->value,
                self::PHOTOGRAPHE->value,
                self::USER->value
            ],
            self::ADMIN->value => [
                self::PHOTOGRAPHE->value,
                self::USER->value
            ],
            self::PHOTOGRAPHE->value => [
                self::USER->value
            ],
        ];
    }
    
    /**
     * Vérifie si ce rôle a accès à un rôle requis
     */
    public function hasAccess(self $requiredRole): bool
    {
        if ($this === $requiredRole) {
            return true;
        }
        
        $hierarchy = self::getHierarchy();
        
        return isset($hierarchy[$this->value]) 
            && in_array($requiredRole->value, $hierarchy[$this->value], true);
    }
    
    /**
     * Retourne tous les rôles accessibles par ce rôle
     */
    public function getAccessibleRoles(): array
    {
        $hierarchy = self::getHierarchy();
        
        return $hierarchy[$this->value] ?? [];
    }
    
    /**
     * Vérifie si c'est un rôle administrateur
     */
    public function isAdmin(): bool
    {
        return $this === self::SUPERADMIN || $this === self::ADMIN;
    }
    
    /**
     * Vérifie si c'est le rôle le plus élevé
     */
    public function isSuperAdmin(): bool
    {
        return $this === self::SUPERADMIN;
    }
    
    /**
     * Retourne le niveau de priorité du rôle (plus haut = plus de droits)
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::SUPERADMIN => 4,
            self::ADMIN => 3,
            self::PHOTOGRAPHE => 2,
            self::USER => 1,
        };
    }
    
    /**
     * Compare deux rôles (retourne true si ce rôle est supérieur ou égal)
     */
    public function isGreaterOrEqual(self $other): bool
    {
        return $this->getPriority() >= $other->getPriority();
    }
    
    /**
     * Retourne le nom affiché du rôle
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::PHOTOGRAPHE => 'Photographe',
            self::USER => 'Utilisateur',
        };
    }
    
    /**
     * Crée un SecurityRole depuis une string
     */
    public static function fromString(string $role): ?self
    {
        return self::tryFrom($role);
    }
    
    /**
     * Retourne tous les rôles disponibles
     */
    public static function getAllRoles(): array
    {
        return array_column(self::cases(), 'value');
    }
} 