<?php

namespace App\DataFixtures;

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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour initialiser tous les référentiels avec les valeurs des enums
 * Pattern appliqué: Service Layer Pattern + Enum Integration
 * Responsabilité: Création des valeurs enum de base pour tous les référentiels
 */
class EnumReferentialFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTypePrisesFromEnum($manager);
        $this->loadTypeVentesFromEnum($manager);
        $this->loadThemesFromEnum($manager);
        $this->loadPochetteIndivsFromEnum($manager);
        $this->loadPochetteFratriesFromEnum($manager);
        $this->loadPlanchesFromEnum($manager);
        
        $manager->flush();
        
        echo "\n✅ ENUMS RÉFÉRENTIELS INITIALISÉS :\n";
        echo "   • " . count(TypePriseEnum::getDefaultValues()) . " types de prise\n";
        echo "   • " . count(TypeVenteEnum::getDefaultValues()) . " types de vente\n";
        echo "   • " . count(ThemeEnum::getDefaultValues()) . " thèmes\n";
        echo "   • " . count(PochetteIndivEnum::getDefaultValues()) . " pochettes individuelles\n";
        echo "   • " . count(PochetteFratrieEnum::getDefaultValues()) . " pochettes fratries\n";
        echo "   • " . count(PlancheEnum::getDefaultValues()) . " planches\n";
    }

    /**
     * Charge les TypePrise depuis l'enum TypePriseEnum
     * Pattern: Single Responsibility - une méthode par type de référentiel
     */
    private function loadTypePrisesFromEnum(ObjectManager $manager): void
    {
        foreach (TypePriseEnum::getDefaultValues() as $enumValue) {
            $typePrise = new TypePrise();
            $typePrise->setLibelle($enumValue->getLibelle());
            $typePrise->setDescription($enumValue->getDescription());
            $typePrise->setActive(true);
            
            $manager->persist($typePrise);
            
            // Créer une référence claire pour l'utiliser dans d'autres fixtures
            $this->addReference('enum_type_prise_' . $enumValue->value, $typePrise);
        }
    }

    /**
     * Charge les TypeVente depuis l'enum TypeVenteEnum
     */
    private function loadTypeVentesFromEnum(ObjectManager $manager): void
    {
        foreach (TypeVenteEnum::getDefaultValues() as $enumValue) {
            $typeVente = new TypeVente();
            $typeVente->setLibelle($enumValue->getLibelle());
            $typeVente->setDescription($enumValue->getDescription());
            $typeVente->setActive(true);
            
            $manager->persist($typeVente);
            
            $this->addReference('enum_type_vente_' . $enumValue->value, $typeVente);
        }
    }

    /**
     * Charge les Themes depuis l'enum ThemeEnum
     */
    private function loadThemesFromEnum(ObjectManager $manager): void
    {
        foreach (ThemeEnum::getDefaultValues() as $enumValue) {
            $theme = new Theme();
            $theme->setLibelle($enumValue->getLibelle());
            $theme->setDescription($enumValue->getDescription());
            $theme->setActive(true);
            
            $manager->persist($theme);
            
            $this->addReference('enum_theme_' . $enumValue->value, $theme);
        }
    }

    /**
     * Charge les PochetteIndiv depuis l'enum PochetteIndivEnum
     */
    private function loadPochetteIndivsFromEnum(ObjectManager $manager): void
    {
        foreach (PochetteIndivEnum::getDefaultValues() as $enumValue) {
            $pochetteIndiv = new PochetteIndiv();
            $pochetteIndiv->setLibelle($enumValue->getLibelle());
            $pochetteIndiv->setDescription($enumValue->getDescription());
            $pochetteIndiv->setActive(true);
            
            $manager->persist($pochetteIndiv);
            
            $this->addReference('enum_pochette_indiv_' . $enumValue->value, $pochetteIndiv);
        }
    }

    /**
     * Charge les PochetteFratrie depuis l'enum PochetteFratrieEnum
     */
    private function loadPochetteFratriesFromEnum(ObjectManager $manager): void
    {
        foreach (PochetteFratrieEnum::getDefaultValues() as $enumValue) {
            $pochetteFratrie = new PochetteFratrie();
            $pochetteFratrie->setLibelle($enumValue->getLibelle());
            $pochetteFratrie->setDescription($enumValue->getDescription());
            $pochetteFratrie->setActive(true);
            
            $manager->persist($pochetteFratrie);
            
            $this->addReference('enum_pochette_fratrie_' . $enumValue->value, $pochetteFratrie);
        }
    }

    /**
     * Charge les Planches depuis l'enum PlancheEnum
     */
    private function loadPlanchesFromEnum(ObjectManager $manager): void
    {
        foreach (PlancheEnum::getDefaultValues() as $enumValue) {
            $planche = new Planche();
            $planche->setLibelle($enumValue->getLibelle());
            $planche->setDescription($enumValue->getDescription());
            $planche->setActive(true);
            
            $manager->persist($planche);
            
            $this->addReference('enum_planche_' . $enumValue->value, $planche);
        }
    }

    /**
     * Groupe de cette fixture pour le chargement sélectif
     */
    public static function getGroups(): array
    {
        return ['enum', 'referential', 'core'];
    }

    /**
     * Pas de dépendances - les enums sont chargés en premier
     */
    public function getDependencies(): array
    {
        return [];
    }
} 