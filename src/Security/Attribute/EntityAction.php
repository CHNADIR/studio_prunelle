<?php

namespace App\Security\Attribute;

/**
 * Enum centralisé pour toutes les actions d'entités
 * Pattern appliqué: Enum Pattern (patterns.md)
 * 
 * Centralise toutes les actions pour éviter les duplications
 * entre les différents Voters et garantir la cohérence
 */
enum EntityAction: string
{
    // === ACTIONS CRUD BASIQUES ===
    case VIEW   = 'VIEW';
    case CREATE = 'CREATE';
    case EDIT   = 'EDIT';
    case DELETE = 'DELETE';
    
    // === ACTIONS SPÉCIALISÉES RÉFÉRENTIELS ===
    case REFERENTIEL_VIEW = 'REFERENTIEL_VIEW';
    case REFERENTIEL_CREATE = 'REFERENTIEL_CREATE';
    case REFERENTIEL_EDIT = 'REFERENTIEL_EDIT';
    case REFERENTIEL_DELETE = 'REFERENTIEL_DELETE';
    case REFERENTIEL_DELETE_ENUM = 'REFERENTIEL_DELETE_ENUM';
    
    // === ACTIONS SPÉCIALISÉES PRISES DE VUE ===
    case PRISEDEVUE_VIEW = 'PRISEDEVUE_VIEW';
    case PRISEDEVUE_EDIT = 'PRISEDEVUE_EDIT';
    case PRISEDEVUE_DELETE = 'PRISEDEVUE_DELETE';
    case PRISEDEVUE_COMMENT = 'PRISEDEVUE_COMMENT';
    
    // === ACTIONS SPÉCIALISÉES ÉCOLES ===
    case ECOLE_VIEW = 'ECOLE_VIEW';
    case ECOLE_CREATE = 'ECOLE_CREATE';
    case ECOLE_EDIT = 'ECOLE_EDIT';
    case ECOLE_DELETE = 'ECOLE_DELETE';
    
    // === ACTIONS SPÉCIALISÉES UTILISATEURS ===
    case USER_MANAGE_ROLES = 'USER_MANAGE_ROLES';
    case USER_RESET_PASSWORD = 'USER_RESET_PASSWORD';
    
    /**
     * Retourne toutes les actions CRUD basiques
     */
    public static function getBasicActions(): array
    {
        return [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }
    
    /**
     * Retourne toutes les actions pour les référentiels
     */
    public static function getReferentialActions(): array
    {
        return [
            self::REFERENTIEL_VIEW,
            self::REFERENTIEL_CREATE,
            self::REFERENTIEL_EDIT,
            self::REFERENTIEL_DELETE,
            self::REFERENTIEL_DELETE_ENUM,
        ];
    }
    
    /**
     * Retourne toutes les actions pour les prises de vue
     */
    public static function getPriseDeVueActions(): array
    {
        return [
            self::PRISEDEVUE_VIEW,
            self::PRISEDEVUE_EDIT,
            self::PRISEDEVUE_DELETE,
            self::PRISEDEVUE_COMMENT,
        ];
    }
    
    /**
     * Retourne toutes les actions pour les écoles
     */
    public static function getEcoleActions(): array
    {
        return [
            self::ECOLE_VIEW,
            self::ECOLE_CREATE,
            self::ECOLE_EDIT,
            self::ECOLE_DELETE,
        ];
    }
    
    /**
     * Retourne toutes les actions disponibles
     */
    public static function getAllActions(): array
    {
        return array_column(self::cases(), 'value');
    }
    
    /**
     * Vérifie si une action est de type CRUD basique
     */
    public function isBasicCrud(): bool
    {
        return in_array($this, self::getBasicActions(), true);
    }
    
    /**
     * Vérifie si une action concerne les référentiels
     */
    public function isReferentialAction(): bool
    {
        return in_array($this, self::getReferentialActions(), true);
    }
}
