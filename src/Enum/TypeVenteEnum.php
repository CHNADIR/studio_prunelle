<?php

namespace App\Enum;

/**
 * Énumération des types de vente par défaut selon le cahier des charges
 * Ces valeurs sont utilisées pour initialiser les référentiels
 * L'admin peut ajouter d'autres valeurs via l'interface
 */
enum TypeVenteEnum: string
{
    case BON_COMMANDE = 'bon de commande';
    case INTERNET = 'internet';
    case HYBRIDE = 'hybride';
    case VENTE_DIRECT = 'vente direct';
    
    /**
     * Retourne le libellé français pour l'affichage
     */
    public function getLibelle(): string
    {
        return match($this) {
            self::BON_COMMANDE => 'Bon de commande',
            self::INTERNET => 'Vente internet',
            self::HYBRIDE => 'Vente hybride',
            self::VENTE_DIRECT => 'Vente directe',
        };
    }
    
    /**
     * Retourne la description détaillée
     */
    public function getDescription(): string
    {
        return match($this) {
            self::BON_COMMANDE => 'Commande via bon papier traditionnel',
            self::INTERNET => 'Commande en ligne via plateforme web',
            self::HYBRIDE => 'Combinaison bon de commande + internet',
            self::VENTE_DIRECT => 'Vente sur place lors de la séance photo',
        };
    }
    
    /**
     * Retourne toutes les valeurs par défaut pour l'initialisation
     */
    public static function getDefaultValues(): array
    {
        return [
            self::BON_COMMANDE,
            self::INTERNET,
            self::HYBRIDE,
            self::VENTE_DIRECT
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