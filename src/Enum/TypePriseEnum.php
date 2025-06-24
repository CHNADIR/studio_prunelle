<?php

namespace App\Enum;

/**
 * Énumération des types de prise par défaut selon le cahier des charges
 * Ces valeurs sont utilisées pour initialiser les référentiels
 * L'admin peut ajouter d'autres valeurs via l'interface
 */
enum TypePriseEnum: string
{
    case INDIVIDUEL = 'individuel';
    case INDIVIDUEL_GROUPE = 'individuel + groupe'; 
    case GROUPE_SEUL = 'groupe seul';
    
    /**
     * Retourne le libellé français pour l'affichage
     */
    public function getLibelle(): string
    {
        return match($this) {
            self::INDIVIDUEL => 'Photo individuelle',
            self::INDIVIDUEL_GROUPE => 'Photo individuelle + groupe',
            self::GROUPE_SEUL => 'Photo de groupe seul',
        };
    }
    
    /**
     * Retourne la description détaillée
     */
    public function getDescription(): string
    {
        return match($this) {
            self::INDIVIDUEL => 'Portrait individuel d\'élève uniquement',
            self::INDIVIDUEL_GROUPE => 'Portrait individuel + photo de classe ou groupe',
            self::GROUPE_SEUL => 'Photo de classe ou groupe uniquement',
        };
    }
    
    /**
     * Retourne toutes les valeurs par défaut pour l'initialisation
     */
    public static function getDefaultValues(): array
    {
        return [
            self::INDIVIDUEL,
            self::INDIVIDUEL_GROUPE, 
            self::GROUPE_SEUL
        ];
    }
    
    /**
     * Vérifie si une valeur existe dans l'enum
     */
    public static function exists(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'));
    }
} 