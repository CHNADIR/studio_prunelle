<?php

namespace App\Security\Voter;

use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\Theme;
use App\Entity\PochetteIndiv;
use App\Entity\PochetteFratrie;
use App\Entity\Planche;
use App\Security\Attribute\EntityAction;
use App\Security\Attribute\SecurityRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Voter pour tous les référentiels - Version temporaire
 * Pattern appliqué: Template Method Pattern (patterns.md)
 * 
 * NOTE: Ce Voter sera remplacé par des Voters spécialisés (TypePriseVoter, etc.)
 * mais est conservé temporairement pour éviter les breaking changes
 */
final class ReferentielVoter extends AbstractEntityVoter
{
    // Actions spécialisées référentiels (compatibilité)
    public const REFERENTIEL_VIEW = 'REFERENTIEL_VIEW';
    public const REFERENTIEL_CREATE = 'REFERENTIEL_CREATE';
    public const REFERENTIEL_EDIT = 'REFERENTIEL_EDIT';
    public const REFERENTIEL_DELETE = 'REFERENTIEL_DELETE';
    public const REFERENTIEL_DELETE_ENUM = 'REFERENTIEL_DELETE_ENUM';

    public function __construct(
        protected \App\Security\Service\SecurityHelper $securityHelper,
        private Security $security
    ) {
        parent::__construct($securityHelper);
    }

    // === CONFIGURATION DU VOTER (MÉTHODES ABSTRAITES) ===

    protected function getSupportedEntities(): array
    {
        return [
            TypePrise::class,
            TypeVente::class,
            Theme::class,
            PochetteIndiv::class,
            PochetteFratrie::class,
            Planche::class
        ];
    }

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
            // Actions compatibilité
            self::REFERENTIEL_VIEW,
            self::REFERENTIEL_CREATE,
            self::REFERENTIEL_EDIT,
            self::REFERENTIEL_DELETE,
            self::REFERENTIEL_DELETE_ENUM,
        ];
    }

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
            // Actions compatibilité
            self::REFERENTIEL_VIEW => SecurityRole::ADMIN,
            self::REFERENTIEL_CREATE => SecurityRole::ADMIN,
            self::REFERENTIEL_EDIT => SecurityRole::ADMIN,
            self::REFERENTIEL_DELETE => SecurityRole::ADMIN,
            self::REFERENTIEL_DELETE_ENUM => SecurityRole::SUPERADMIN,
        ];
    }

    // === LOGIQUE PERSONNALISÉE ===

    protected function customVoteLogic(string $attribute, mixed $subject, TokenInterface $token): ?bool
    {
        // Logique spécifique selon l'action
        return match ($attribute) {
            self::REFERENTIEL_VIEW, EntityAction::REFERENTIEL_VIEW->value, EntityAction::VIEW->value => 
                $this->canView($subject, $token),
            self::REFERENTIEL_CREATE, EntityAction::REFERENTIEL_CREATE->value, EntityAction::CREATE->value => 
                $this->canCreate($subject, $token),
            self::REFERENTIEL_EDIT, EntityAction::REFERENTIEL_EDIT->value, EntityAction::EDIT->value => 
                $this->canEdit($subject, $token),
            self::REFERENTIEL_DELETE, EntityAction::REFERENTIEL_DELETE->value, EntityAction::DELETE->value => 
                $this->canDelete($subject, $token),
            self::REFERENTIEL_DELETE_ENUM, EntityAction::REFERENTIEL_DELETE_ENUM->value => 
                $this->canDeleteEnum($subject, $token),
            default => null // Continuer avec la logique par défaut
        };
    }

    // === MÉTHODES PRIVÉES DE LOGIQUE MÉTIER ===

    /**
     * Peut voir un référentiel
     */
    private function canView(mixed $subject, TokenInterface $token): bool
    {
        // Admin et SuperAdmin peuvent voir tous les référentiels
        return $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Peut créer un référentiel
     */
    private function canCreate(mixed $subject, TokenInterface $token): bool
    {
        // Admin et SuperAdmin peuvent créer des référentiels
        return $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Peut modifier un référentiel
     */
    private function canEdit(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }

        // Vérification spéciale pour les valeurs d'énums
        if ($this->isEnumValue($subject)) {
            // Les SuperAdmin peuvent modifier les valeurs d'énums
            // Les Admin normaux ont un avertissement mais peuvent le faire
            return $this->security->isGranted('ROLE_ADMIN');
        }

        return true;
    }

    /**
     * Peut supprimer un référentiel
     */
    private function canDelete(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->security->isGranted('ROLE_ADMIN')) {
            return false;
        }

        // Vérifier que l'entité n'est pas utilisée
        if ($this->isEntityUsed($subject)) {
            return false;
        }

        // Pour les valeurs d'énums, nécessite une confirmation spéciale
        if ($this->isEnumValue($subject)) {
            return $this->security->isGranted('ROLE_SUPERADMIN');
        }

        return true;
    }

    /**
     * Peut supprimer une valeur d'enum (action spéciale)
     */
    private function canDeleteEnum(mixed $subject, TokenInterface $token): bool
    {
        if (!$subject || !$this->isEnumValue($subject)) {
            return false;
        }

        // Seuls les SuperAdmin peuvent supprimer les valeurs d'énums
        return $this->security->isGranted('ROLE_SUPERADMIN');
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

        // Vérifier selon le type d'entité
        return match (get_class($subject)) {
            TypePrise::class => $this->checkEnumValue($libelle, \App\Enum\TypePriseEnum::getDefaultValues()),
            TypeVente::class => $this->checkEnumValue($libelle, \App\Enum\TypeVenteEnum::getDefaultValues()),
            Theme::class => $this->checkEnumValue($libelle, \App\Enum\ThemeEnum::getDefaultValues()),
            PochetteIndiv::class => $this->checkEnumValue($libelle, \App\Enum\PochetteIndivEnum::getDefaultValues()),
            PochetteFratrie::class => $this->checkEnumValue($libelle, \App\Enum\PochetteFratrieEnum::getDefaultValues()),
            Planche::class => $this->checkEnumValue($libelle, \App\Enum\PlancheEnum::getDefaultValues()),
            default => false
        };
    }

    /**
     * Helper pour vérifier si un libellé correspond à un enum
     */
    private function checkEnumValue(string $libelle, array $enumValues): bool
    {
        foreach ($enumValues as $enumValue) {
            if (method_exists($enumValue, 'getLibelle') && $enumValue->getLibelle() === $libelle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifie si une entité est utilisée (relations)
     * Surcharge la méthode de AbstractEntityVoter pour une logique spécialisée
     */
    protected function isEntityUsed(mixed $subject): bool
    {
        if (!$subject || !method_exists($subject, 'getPrisesDeVue')) {
            return false;
        }

        $prisesDeVue = $subject->getPrisesDeVue();
        
        // Si c'est une collection, vérifier qu'elle est vide
        if ($prisesDeVue instanceof \Doctrine\Common\Collections\Collection) {
            return !$prisesDeVue->isEmpty();
        }
        
        return $prisesDeVue !== null;
    }
}
