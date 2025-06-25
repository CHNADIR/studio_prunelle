<?php

namespace App\Security\Voter;

use App\Entity\Theme;

/**
 * Voter spécialisé pour Theme - Version optimisée
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Utilise AbstractReferentialVoter pour factoriser toute la logique
 * des référentiels et gérer les énums automatiquement
 */
final class ThemeVoter extends AbstractReferentialVoter
{
    // === CONFIGURATION MINIMALE ===

    protected function getSupportedEntities(): array
    {
        return [Theme::class];
    }

    protected function getEnumClass(): string
    {
        return \App\Enum\ThemeEnum::class;
    }

    // C'est tout ! Toute la logique est héritée de AbstractReferentialVoter
    // - Actions supportées (CRUD + REFERENTIEL_*)
    // - Rôles requis (ADMIN pour CRUD, SUPERADMIN pour DELETE_ENUM)
    // - Vérifications d'énums automatiques
    // - Validation des contraintes métier
    // - Gestion des entités utilisées
} 