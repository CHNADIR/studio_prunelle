<?php

namespace App\Enum;

/**
 * Énumération des thèmes par défaut selon le cahier des charges
 * Ces valeurs sont utilisées pour initialiser les référentiels
 * L'admin peut ajouter d'autres valeurs via l'interface
 */
enum ThemeEnum: string
{
    case NATURE = 'nature';
    case ECOLE = 'ecole';
    case MUSIQUE = 'musique';
    
    /**
     * Retourne le libellé français pour l'affichage
     */
    public function getLibelle(): string
    {
        return match($this) {
            self::NATURE => 'Nature',
            self::ECOLE => 'École',
            self::MUSIQUE => 'Musique',
        };
    }
    
    /**
     * Retourne la description détaillée
     */
    public function getDescription(): string
    {
        return match($this) {
            self::NATURE => 'Thème naturel avec décor extérieur ou végétal',
            self::ECOLE => 'Thème scolaire traditionnel en classe ou cour',
            self::MUSIQUE => 'Thème musical avec instruments ou partition',
        };
    }
    
    /**
     * Retourne l'icône associée
     */
    public function getIcon(): string
    {
        return match($this) {
            self::NATURE => 'bi-tree',
            self::ECOLE => 'bi-building',
            self::MUSIQUE => 'bi-music-note',
        };
    }
    
    /**
     * Retourne toutes les valeurs par défaut pour l'initialisation
     */
    public static function getDefaultValues(): array
    {
        return [
            self::NATURE,
            self::ECOLE,
            self::MUSIQUE
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