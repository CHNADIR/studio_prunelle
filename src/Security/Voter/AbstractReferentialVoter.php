<?php

namespace App\Security\Voter;

use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter abstrait spécialisé pour les entités référentielles
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * Spécialise la logique des référentiels avec gestion des énums
 * et des validations métier spécifiques
 */
abstract class AbstractReferentialVoter extends AbstractEntityVoter
{
    // === MÉTHODES ABSTRAITES SPÉCIALISÉES ===

    /**
     * Retourne la classe de l'enum associé à ce référentiel
     */
    abstract protected function getEnumClass(): string;

    // === CONFIGURATION COMMUNE DES RÉFÉRENTIELS ===

    /**
     * Actions supportées par défaut pour tous les référentiels
     */
    protected function getSupportedActions(): array
    {
        return [
            EntityAction::VIEW,
            EntityAction::CREATE,
            EntityAction::EDIT,
            EntityAction::DELETE,
            EntityAction::REFERENTIEL_VIEW,
            EntityAction::REFERENTIEL_CREATE,
            EntityAction::REFERENTIEL_EDIT,
            EntityAction::REFERENTIEL_DELETE,
            EntityAction::REFERENTIEL_DELETE_ENUM,
        ];
    }

    /**
     * Rôles requis par défaut pour les référentiels
     */
    protected function getRequiredRoles(): array
    {
        return [
            EntityAction::VIEW->value => SecurityRole::ADMIN,
            EntityAction::CREATE->value => SecurityRole::ADMIN,
            EntityAction::EDIT->value => SecurityRole::ADMIN,
            EntityAction::DELETE->value => SecurityRole::ADMIN,
            EntityAction::REFERENTIEL_VIEW->value => SecurityRole::ADMIN,
            EntityAction::REFERENTIEL_CREATE->value => SecurityRole::ADMIN,
            EntityAction::REFERENTIEL_EDIT->value => SecurityRole::ADMIN,
            EntityAction::REFERENTIEL_DELETE->value => SecurityRole::ADMIN,
            EntityAction::REFERENTIEL_DELETE_ENUM->value => SecurityRole::SUPERADMIN,
        ];
    }

    // === LOGIQUE SPÉCIALISÉE DES RÉFÉRENTIELS ===

    /**
     * Logique personnalisée pour les référentiels
     */
    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        // Vérifications spéciales pour la suppression d'énums
        if ($attribute === EntityAction::REFERENTIEL_DELETE_ENUM->value) {
            return $this->canDeleteEnum($subject, $token);
        }

        // Vérifications spéciales pour la suppression
        if ($attribute === EntityAction::DELETE->value || $attribute === EntityAction::REFERENTIEL_DELETE->value) {
            return $this->canDelete($subject, $token);
        }

        // Vérifications spéciales pour la modification d'énums
        if ($attribute === EntityAction::EDIT->value || $attribute === EntityAction::REFERENTIEL_EDIT->value) {
            if ($this->isEnumValue($subject)) {
                return $this->canEditEnum($subject, $token);
            }
        }

        return null; // Continuer avec la logique par défaut
    }

    // === MÉTHODES SPÉCIALISÉES POUR LES RÉFÉRENTIELS ===

    /**
     * Vérifie si l'utilisateur peut supprimer une valeur d'enum
     */
    protected function canDeleteEnum(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->isEnumValue($subject)) {
            return false;
        }

        // Seuls les SuperAdmin peuvent supprimer les valeurs d'énums
        return $this->securityHelper->hasMinimumRole($token, SecurityRole::SUPERADMIN);
    }

    /**
     * Vérifie si l'utilisateur peut modifier une valeur d'enum
     */
    protected function canEditEnum(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->isEnumValue($subject)) {
            return true; // Ce n'est pas un enum, autoriser
        }

        // Les Admin peuvent modifier les énums avec avertissement
        // Les SuperAdmin peuvent le faire sans restriction
        return $this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN);
    }

    /**
     * Vérifie si l'utilisateur peut supprimer un référentiel
     */
    protected function canDelete(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->securityHelper->hasMinimumRole($token, SecurityRole::ADMIN)) {
            return false;
        }

        // Ne pas supprimer si l'entité est utilisée
        if ($this->isEntityUsed($subject)) {
            return false;
        }

        // Pour les valeurs d'énums, nécessite SuperAdmin
        if ($this->isEnumValue($subject)) {
            return $this->securityHelper->hasMinimumRole($token, SecurityRole::SUPERADMIN);
        }

        return true;
    }

    /**
     * Vérifie si une entité correspond à une valeur d'enum
     */
    protected function isEnumValue(mixed $subject): bool
    {
        if (!$subject || !method_exists($subject, 'getLibelle')) {
            return false;
        }

        $libelle = $subject->getLibelle();
        $enumClass = $this->getEnumClass();

        // Vérifier si la classe enum existe et a la méthode getDefaultValues
        if (!class_exists($enumClass) || !method_exists($enumClass, 'getDefaultValues')) {
            return false;
        }

        $enumValues = $enumClass::getDefaultValues();
        
        foreach ($enumValues as $enumValue) {
            if (method_exists($enumValue, 'getLibelle') && $enumValue->getLibelle() === $libelle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne des informations sur le référentiel pour le debug/logging
     */
    protected function getReferentialInfo(mixed $subject): array
    {
        if (!$subject) {
            return [];
        }

        return [
            'class' => get_class($subject),
            'id' => method_exists($subject, 'getId') ? $subject->getId() : null,
            'libelle' => method_exists($subject, 'getLibelle') ? $subject->getLibelle() : null,
            'is_enum_value' => $this->isEnumValue($subject),
            'is_used' => $this->isEntityUsed($subject),
            'enum_class' => $this->getEnumClass(),
        ];
    }

    /**
     * Vérifie les contraintes métier spécifiques aux référentiels
     */
    protected function validateReferentialConstraints(mixed $subject, string $attribute): array
    {
        $errors = [];

        if (!$subject) {
            return $errors;
        }

        // Vérification de suppression d'entité utilisée
        if (($attribute === EntityAction::DELETE->value || $attribute === EntityAction::REFERENTIEL_DELETE->value) 
            && $this->isEntityUsed($subject)) {
            $errors[] = 'Cette entité ne peut pas être supprimée car elle est utilisée dans des prises de vue';
        }

        // Vérification de suppression de valeur d'enum
        if ($attribute === EntityAction::REFERENTIEL_DELETE_ENUM->value && !$this->isEnumValue($subject)) {
            $errors[] = 'Cette entité n\'est pas une valeur d\'enum et ne peut pas être supprimée avec cette action';
        }

        return $errors;
    }

    /**
     * Retourne un message d'aide pour les actions sur les référentiels
     */
    protected function getReferentialActionHelp(string $attribute): string
    {
        return match ($attribute) {
            EntityAction::REFERENTIEL_DELETE_ENUM->value => 
                'Suppression de valeur d\'enum - Réservée aux SuperAdmin uniquement',
            EntityAction::REFERENTIEL_DELETE->value => 
                'Suppression impossible si l\'entité est utilisée dans des prises de vue',
            EntityAction::REFERENTIEL_EDIT->value => 
                'Modification d\'une valeur d\'enum nécessite une attention particulière',
            default => 'Action standard sur référentiel - Réservée aux Administrateurs',
        };
    }
} 