<?php

namespace App\Service;

use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Entity\PochetteIndiv;
use App\Entity\PochetteFratrie;
use App\Entity\Planche;
use App\Enum\ThemeEnum;
use App\Enum\TypePriseEnum;
use App\Enum\TypeVenteEnum;
use App\Enum\PochetteIndivEnum;
use App\Enum\PochetteFratrieEnum;
use App\Enum\PlancheEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service pour initialiser automatiquement les valeurs des enums en base de données
 * Conforme aux patterns recommandés : Service Layer + Single Responsibility
 * Pattern: Enum Synchronization Service
 */
class EnumInitializerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Initialise tous les référentiels avec les valeurs des enums
     * Pattern: Facade - une seule méthode pour tout initialiser
     */
    public function initializeAllEnums(): array
    {
        $result = [
            'success' => true,
            'initialized' => [],
            'errors' => []
        ];

        try {
            $result['initialized']['TypePrise'] = $this->initializeTypePrises();
            $result['initialized']['TypeVente'] = $this->initializeTypeVentes();
            $result['initialized']['Theme'] = $this->initializeThemes();
            $result['initialized']['PochetteIndiv'] = $this->initializePochetteIndivs();
            $result['initialized']['PochetteFratrie'] = $this->initializePochetteFratries();
            $result['initialized']['Planche'] = $this->initializePlanches();

            $this->entityManager->flush();

            $this->logger->info('Enums initialisés avec succès', $result['initialized']);

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->logger->error('Erreur lors de l\'initialisation des enums', ['error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Initialise les TypePrise depuis l'enum
     * Pattern: Single Responsibility
     */
    public function initializeTypePrises(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(TypePrise::class);

        foreach (TypePriseEnum::getDefaultValues() as $enumValue) {
            // Vérifier si l'entité existe déjà
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $typePrise = new TypePrise();
                $typePrise->setLibelle($enumValue->getLibelle());
                $typePrise->setDescription($enumValue->getDescription());
                $typePrise->setActive(true);
                
                $this->entityManager->persist($typePrise);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialise les TypeVente depuis l'enum
     */
    public function initializeTypeVentes(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(TypeVente::class);

        foreach (TypeVenteEnum::getDefaultValues() as $enumValue) {
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $typeVente = new TypeVente();
                $typeVente->setLibelle($enumValue->getLibelle());
                $typeVente->setDescription($enumValue->getDescription());
                $typeVente->setActive(true);
                
                $this->entityManager->persist($typeVente);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialise les Themes depuis l'enum
     */
    public function initializeThemes(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(Theme::class);

        foreach (ThemeEnum::getDefaultValues() as $enumValue) {
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $theme = new Theme();
                $theme->setLibelle($enumValue->getLibelle());
                $theme->setDescription($enumValue->getDescription());
                $theme->setActive(true);
                
                $this->entityManager->persist($theme);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialise les PochetteIndiv depuis l'enum
     */
    public function initializePochetteIndivs(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(PochetteIndiv::class);

        foreach (PochetteIndivEnum::getDefaultValues() as $enumValue) {
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $pochetteIndiv = new PochetteIndiv();
                $pochetteIndiv->setLibelle($enumValue->getLibelle());
                $pochetteIndiv->setDescription($enumValue->getDescription());
                $pochetteIndiv->setActive(true);
                
                $this->entityManager->persist($pochetteIndiv);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialise les PochetteFratrie depuis l'enum
     */
    public function initializePochetteFratries(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(PochetteFratrie::class);

        foreach (PochetteFratrieEnum::getDefaultValues() as $enumValue) {
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $pochetteFratrie = new PochetteFratrie();
                $pochetteFratrie->setLibelle($enumValue->getLibelle());
                $pochetteFratrie->setDescription($enumValue->getDescription());
                $pochetteFratrie->setActive(true);
                
                $this->entityManager->persist($pochetteFratrie);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Initialise les Planches depuis l'enum
     */
    public function initializePlanches(): int
    {
        $count = 0;
        $repository = $this->entityManager->getRepository(Planche::class);

        foreach (PlancheEnum::getDefaultValues() as $enumValue) {
            $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
            
            if (!$existing) {
                $planche = new Planche();
                $planche->setLibelle($enumValue->getLibelle());
                $planche->setDescription($enumValue->getDescription());
                $planche->setActive(true);
                
                $this->entityManager->persist($planche);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Vérifie si tous les enums sont bien synchronisés en base
     * Pattern: Validation Service
     */
    public function validateEnumsSynchronization(): array
    {
        $result = [
            'synchronized' => true,
            'missing' => [],
            'summary' => []
        ];

        // Vérifier chaque type d'enum
        $enumTypes = [
            'TypePrise' => [TypePrise::class, TypePriseEnum::getDefaultValues()],
            'TypeVente' => [TypeVente::class, TypeVenteEnum::getDefaultValues()],
            'Theme' => [Theme::class, ThemeEnum::getDefaultValues()],
            'PochetteIndiv' => [PochetteIndiv::class, PochetteIndivEnum::getDefaultValues()],
            'PochetteFratrie' => [PochetteFratrie::class, PochetteFratrieEnum::getDefaultValues()],
            'Planche' => [Planche::class, PlancheEnum::getDefaultValues()]
        ];

        foreach ($enumTypes as $type => [$entityClass, $enumValues]) {
            $repository = $this->entityManager->getRepository($entityClass);
            $missing = [];

            foreach ($enumValues as $enumValue) {
                $existing = $repository->findOneBy(['libelle' => $enumValue->getLibelle()]);
                if (!$existing) {
                    $missing[] = $enumValue->getLibelle();
                }
            }

            $result['summary'][$type] = [
                'total_enum' => count($enumValues),
                'missing_count' => count($missing),
                'synchronized' => count($missing) === 0
            ];

            if (!empty($missing)) {
                $result['synchronized'] = false;
                $result['missing'][$type] = $missing;
            }
        }

        return $result;
    }
} 