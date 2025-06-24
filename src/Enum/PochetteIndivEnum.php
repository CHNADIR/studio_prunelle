<?php

namespace App\Enum;

/**
 * Énumération des pochettes individuelles par défaut selon le cahier des charges
 * Ces valeurs sont utilisées pour initialiser les référentiels
 * L'admin peut ajouter d'autres valeurs via l'interface
 */
enum PochetteIndivEnum: string
{
    case PAGE_SOUVENIR = 'page souvenir';
    case MULTI_MARQUE_PAGE = 'multi avec marque-page';
    case FORMAT_9X13 = '9x13';
    case FORMAT_10X15 = '10x15';
    case IDENTITES = 'identités';
    case PORTRAIT_COULEUR = 'portrait couleur';
    case PORTRAIT_NB = 'portrait noir et blanc';
    case DOUBLE_13X18 = 'double 13x18';
    case QUATRE_9X13 = 'quatre 9x13';
    case PHOTO_GROUPE = 'photo de groupe';
    
    /**
     * Retourne le libellé français pour l'affichage
     */
    public function getLibelle(): string
    {
        return match($this) {
            self::PAGE_SOUVENIR => 'Page souvenir',
            self::MULTI_MARQUE_PAGE => 'Multi avec marque-page',
            self::FORMAT_9X13 => 'Format 9x13',
            self::FORMAT_10X15 => 'Format 10x15',
            self::IDENTITES => 'Photos d\'identité',
            self::PORTRAIT_COULEUR => 'Portrait couleur',
            self::PORTRAIT_NB => 'Portrait noir et blanc',
            self::DOUBLE_13X18 => 'Double 13x18',
            self::QUATRE_9X13 => 'Quatre 9x13',
            self::PHOTO_GROUPE => 'Photo de groupe',
        };
    }
    
    /**
     * Retourne la description détaillée
     */
    public function getDescription(): string
    {
        return match($this) {
            self::PAGE_SOUVENIR => 'Page souvenir personnalisée avec photo et texte',
            self::MULTI_MARQUE_PAGE => 'Planche multiple avec marque-page intégré',
            self::FORMAT_9X13 => 'Photo standard format 9x13 cm',
            self::FORMAT_10X15 => 'Photo standard format 10x15 cm',
            self::IDENTITES => 'Planche de photos d\'identité réglementaires',
            self::PORTRAIT_COULEUR => 'Portrait individuel en couleur',
            self::PORTRAIT_NB => 'Portrait individuel en noir et blanc',
            self::DOUBLE_13X18 => 'Double tirage format 13x18 cm',
            self::QUATRE_9X13 => 'Planche de quatre photos 9x13 cm',
            self::PHOTO_GROUPE => 'Photo de groupe avec l\'élève',
        };
    }
    
    /**
     * Retourne la catégorie de la pochette
     */
    public function getCategorie(): string
    {
        return match($this) {
            self::PAGE_SOUVENIR, self::MULTI_MARQUE_PAGE => 'Premium',
            self::FORMAT_9X13, self::FORMAT_10X15 => 'Standard',
            self::IDENTITES => 'Officiel',
            self::PORTRAIT_COULEUR, self::PORTRAIT_NB => 'Portrait',
            self::DOUBLE_13X18, self::QUATRE_9X13 => 'Multiple',
            self::PHOTO_GROUPE => 'Groupe',
        };
    }
    
    /**
     * Retourne l'icône Bootstrap Icons pour l'affichage
     */
    public function getIcon(): string
    {
        return match($this) {
            self::PAGE_SOUVENIR => 'bi-book',
            self::MULTI_MARQUE_PAGE => 'bi-bookmark-star',
            self::FORMAT_9X13, self::FORMAT_10X15 => 'bi-image',
            self::IDENTITES => 'bi-person-badge',
            self::PORTRAIT_COULEUR => 'bi-palette',
            self::PORTRAIT_NB => 'bi-circle-half',
            self::DOUBLE_13X18, self::QUATRE_9X13 => 'bi-grid-3x3',
            self::PHOTO_GROUPE => 'bi-people',
        };
    }
    
    /**
     * Retourne toutes les valeurs par défaut pour l'initialisation
     */
    public static function getDefaultValues(): array
    {
        return [
            self::PAGE_SOUVENIR,
            self::MULTI_MARQUE_PAGE,
            self::FORMAT_9X13,
            self::FORMAT_10X15,
            self::IDENTITES,
            self::PORTRAIT_COULEUR,
            self::PORTRAIT_NB,
            self::DOUBLE_13X18,
            self::QUATRE_9X13,
            self::PHOTO_GROUPE
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