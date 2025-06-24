<?php

namespace App\Service;

use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\Theme;
use App\Entity\PochetteIndiv;
use App\Entity\PochetteFratrie;
use App\Entity\Planche;
use App\Enum\TypePriseEnum;
use App\Enum\TypeVenteEnum;
use App\Enum\ThemeEnum;
use App\Enum\PochetteIndivEnum;
use App\Enum\PochetteFratrieEnum;
use App\Enum\PlancheEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de gestion des référentiels selon les patterns recommandés
 * Pattern appliqué: Service Layer Pattern (patterns.md)
 * Responsabilité: Validation métier et persistance des référentiels avec énums
 */
class ReferentialManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    /**
     * Sauvegarde une entité référentiel avec validation
     * Pattern: Service Layer - validation centralisée avec énums
     */
    public function save(object $entity): array
    {
        try {
            // Validation avec contraintes Symfony
            $violations = $this->validator->validate($entity);
            
            if (count($violations) > 0) {
                return [
                    'success' => false,
                    'errors' => $violations
                ];
            }

            // Validation métier selon le type d'entité et énums
            $businessValidation = $this->validateBusinessRulesWithEnums($entity);
            if (!$businessValidation['valid']) {
                return [
                    'success' => false,
                    'errors' => $businessValidation['errors']
                ];
            }

            $isNew = $entity->getId() === null;
            
            if ($isNew) {
                $this->entityManager->persist($entity);
            }
            
            $this->entityManager->flush();

            $this->logger->info('Référentiel sauvegardé', [
                'type' => get_class($entity),
                'id' => $entity->getId(),
                'libelle' => method_exists($entity, 'getLibelle') ? $entity->getLibelle() : 'N/A',
                'is_new' => $isNew
            ]);

            return [
                'success' => true, 
                'entity' => $entity,
                'is_new' => $isNew
            ];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la sauvegarde du référentiel', [
                'error' => $e->getMessage(),
                'entity' => get_class($entity)
            ]);

            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Supprime une entité référentiel avec vérification d'usage
     * Pattern: Service Layer - règles métier centralisées
     */
    public function delete(object $entity): array
    {
        try {
            // Vérifier que l'entité peut être supprimée
            if (!$this->canDelete($entity, 'prisesDeVue')) {
                return [
                    'success' => false,
                    'errors' => ['Cette entité ne peut pas être supprimée car elle est utilisée dans des prises de vue.']
                ];
            }

            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $this->logger->info('Référentiel supprimé', [
                'type' => get_class($entity),
                'id' => $entity->getId()
            ]);

            return ['success' => true];

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression du référentiel', [
                'error' => $e->getMessage(),
                'entity' => get_class($entity)
            ]);

            return [
                'success' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Vérifie si une entité peut être supprimée
     * Pattern: Service Layer - logique métier centralisée
     */
    public function canDelete(object $entity, string $relationProperty): bool
    {
        // Vérifier si la méthode existe
        $method = 'get' . ucfirst($relationProperty);
        if (!method_exists($entity, $method)) {
            return true;
        }
        
        // Vérifier si la relation est vide
        $relation = $entity->$method();
        
        // Si c'est une collection
        if ($relation instanceof \Doctrine\Common\Collections\Collection) {
            return $relation->isEmpty();
        }
        
        // Si c'est un objet ou null
        return $relation === null;
    }

    /**
     * Création d'un nouvel élément de référentiel avec validation énums
     */
    public function createReferential(object $entity): array
    {
        $result = $this->save($entity);
        
        if ($result['success']) {
            return [
                'success' => true,
                'id' => $entity->getId(),
                'text' => method_exists($entity, 'getLibelle') ? $entity->getLibelle() : (string) $entity,
                'message' => 'L\'élément a été créé avec succès.'
            ];
        }
        
        return $result;
    }

    /**
     * Validation des règles métier selon les énums du cahier des charges
     * Pattern: Validation centralisée avec règles métier
     */
    private function validateBusinessRulesWithEnums(object $entity): array
    {
        $errors = [];

        // Validation spécifique selon le type d'entité
        switch (get_class($entity)) {
            case TypePrise::class:
                $errors = $this->validateTypePriseWithEnum($entity);
                break;
            case TypeVente::class:
                $errors = $this->validateTypeVenteWithEnum($entity);
                break;
            case Theme::class:
                $errors = $this->validateThemeWithEnum($entity);
                break;
            case PochetteIndiv::class:
                $errors = $this->validatePochetteIndivWithEnum($entity);
                break;
            case PochetteFratrie::class:
                $errors = $this->validatePochetteFratrieWithEnum($entity);
                break;
            case Planche::class:
                $errors = $this->validatePlancheWithEnum($entity);
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validation métier pour TypePrise avec énums
     */
    private function validateTypePriseWithEnum(TypePrise $entity): array
    {
        $errors = [];
        $libelle = $entity->getLibelle();
        
        // Si c'est une valeur d'enum, vérifier la cohérence
        foreach (TypePriseEnum::getDefaultValues() as $enumValue) {
            if ($enumValue->getLibelle() === $libelle) {
                if ($entity->getDescription() !== $enumValue->getDescription()) {
                    $errors[] = "La description ne correspond pas à la valeur standard de l'enum pour '{$libelle}'. Description attendue : '{$enumValue->getDescription()}'";
                }
                break;
            }
        }

        return $errors;
    }

    /**
     * Validation métier pour TypeVente avec énums
     */
    private function validateTypeVenteWithEnum(TypeVente $entity): array
    {
        return $this->validateEntityAgainstEnum($entity, TypeVenteEnum::getDefaultValues());
    }

    /**
     * Validation métier pour Theme avec énums
     */
    private function validateThemeWithEnum(Theme $entity): array
    {
        return $this->validateEntityAgainstEnum($entity, ThemeEnum::getDefaultValues());
    }

    /**
     * Validation métier pour PochetteIndiv avec énums
     */
    private function validatePochetteIndivWithEnum(PochetteIndiv $entity): array
    {
        return $this->validateEntityAgainstEnum($entity, PochetteIndivEnum::getDefaultValues());
    }

    /**
     * Validation métier pour PochetteFratrie avec énums
     */
    private function validatePochetteFratrieWithEnum(PochetteFratrie $entity): array
    {
        return $this->validateEntityAgainstEnum($entity, PochetteFratrieEnum::getDefaultValues());
    }

    /**
     * Validation métier pour Planche avec énums
     */
    private function validatePlancheWithEnum(Planche $entity): array
    {
        return $this->validateEntityAgainstEnum($entity, PlancheEnum::getDefaultValues());
    }

    /**
     * Méthode helper pour valider une entité contre son enum
     */
    private function validateEntityAgainstEnum(object $entity, array $enumValues): array
    {
        $errors = [];
        $libelle = $entity->getLibelle();
        
        foreach ($enumValues as $enumValue) {
            if ($enumValue->getLibelle() === $libelle) {
                if ($entity->getDescription() !== $enumValue->getDescription()) {
                    $errors[] = "La description ne correspond pas à la valeur standard de l'enum pour '{$libelle}'. Description attendue : '{$enumValue->getDescription()}'";
                }
                break;
            }
        }

        return $errors;
    }

    /**
     * Vérifie si une valeur correspond à un enum ou est personnalisée
     */
    public function isEnumValue(object $entity): bool
    {
        $libelle = $entity->getLibelle();
        
        return match (get_class($entity)) {
            TypePrise::class => $this->isValueInEnum($libelle, TypePriseEnum::getDefaultValues()),
            TypeVente::class => $this->isValueInEnum($libelle, TypeVenteEnum::getDefaultValues()),
            Theme::class => $this->isValueInEnum($libelle, ThemeEnum::getDefaultValues()),
            PochetteIndiv::class => $this->isValueInEnum($libelle, PochetteIndivEnum::getDefaultValues()),
            PochetteFratrie::class => $this->isValueInEnum($libelle, PochetteFratrieEnum::getDefaultValues()),
            Planche::class => $this->isValueInEnum($libelle, PlancheEnum::getDefaultValues()),
            default => false
        };
    }

    /**
     * Helper pour vérifier si une valeur existe dans un enum
     */
    private function isValueInEnum(string $libelle, array $enumValues): bool
    {
        foreach ($enumValues as $enumValue) {
            if ($enumValue->getLibelle() === $libelle) {
                return true;
            }
        }
        return false;
    }
}