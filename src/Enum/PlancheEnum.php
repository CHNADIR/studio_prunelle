<?php

namespace App\Enum;

/**
 * Énumération des planches par défaut selon le cahier des charges
 * Ces valeurs sont utilisées pour initialiser les référentiels
 * L'admin peut ajouter d'autres valeurs via l'interface
 */
enum PlancheEnum: string
{
    case POCHETTE_PLASTIQUE = 'pochette en plastique';
    case CARTONNAGE = 'cartonnage';
    case ALBUM = 'album';
    case ALBUMS_NVX_ARRIVANTS = 'albums nvx arrivants';
    
    /**
     * Retourne le libellé français pour l'affichage
     */
    public function getLibelle(): string
    {
        return match($this) {
            self::POCHETTE_PLASTIQUE => 'Pochette en plastique',
            self::CARTONNAGE => 'Cartonnage',
            self::ALBUM => 'Album standard',
            self::ALBUMS_NVX_ARRIVANTS => 'Albums nouveaux arrivants',
        };
    }
    
    /**
     * Retourne la description détaillée
     */
    public function getDescription(): string
    {
        return match($this) {
            self::POCHETTE_PLASTIQUE => 'Pochette plastifiée transparente de protection',
            self::CARTONNAGE => 'Support cartonné rigide personnalisé',
            self::ALBUM => 'Album photo traditionnel avec reliure',
            self::ALBUMS_NVX_ARRIVANTS => 'Album spécial pour les nouveaux élèves',
        };
    }
    
    /**
     * Retourne la catégorie de la planche
     */
    public function getCategorie(): string
    {
        return match($this) {
            self::POCHETTE_PLASTIQUE => 'Protection',
            self::CARTONNAGE => 'Rigide',
            self::ALBUM => 'Reliure',
            self::ALBUMS_NVX_ARRIVANTS => 'Spécialisé',
        };
    }
    
    /**
     * Retourne l'icône associée
     */
    public function getIcon(): string
    {
        return match($this) {
            self::POCHETTE_PLASTIQUE => 'bi-shield-check',
            self::CARTONNAGE => 'bi-card-heading',
            self::ALBUM => 'bi-book',
            self::ALBUMS_NVX_ARRIVANTS => 'bi-person-plus',
        };
    }
    
    /**
     * Retourne toutes les valeurs par défaut pour l'initialisation
     */
    public static function getDefaultValues(): array
    {
        return [
            self::POCHETTE_PLASTIQUE,
            self::CARTONNAGE,
            self::ALBUM,
            self::ALBUMS_NVX_ARRIVANTS
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