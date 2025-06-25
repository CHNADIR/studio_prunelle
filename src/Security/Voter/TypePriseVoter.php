<?php

namespace App\Security\Voter;

use App\Entity\TypePrise;

/**
 * Voter spécialisé pour TypePrise - Version optimisée
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Utilise AbstractReferentialVoter pour factoriser toute la logique
 * des référentiels et gérer les énums automatiquement
 */
final class TypePriseVoter extends AbstractReferentialVoter
{
    // === CONFIGURATION MINIMALE ===

    protected function getSupportedEntities(): array
    {
        return [TypePrise::class];
    }

    protected function getEnumClass(): string
    {
        return \App\Enum\TypePriseEnum::class;
    }

    // C'est tout ! Toute la logique est héritée de AbstractReferentialVoter
    // - Actions supportées (CRUD + REFERENTIEL_*)
    // - Rôles requis (ADMIN pour CRUD, SUPERADMIN pour DELETE_ENUM)
    // - Vérifications d'énums automatiques
    // - Validation des contraintes métier
    // - Gestion des entités utilisées
} 