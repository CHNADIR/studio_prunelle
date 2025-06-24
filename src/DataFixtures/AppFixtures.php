<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures principales de l'application - Orchestrateur
 * Pattern appliqué: Facade Pattern + Chain of Responsibility
 * Responsabilité: Orchestrer le chargement complet des données de test
 */
class AppFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Ce fixture orchestre le chargement de toutes les autres fixtures
        // L'ordre est géré par les dépendances définies dans getDependencies()
        
        // Ajouter quelques données globales si nécessaire
        $this->addGlobalSettings($manager);
        
        $manager->flush();
    }

    /**
     * Ajoute des paramètres globaux de l'application
     */
    private function addGlobalSettings(ObjectManager $manager): void
    {
        // Ici on pourrait ajouter des paramètres globaux, configurations, etc.
        // Pour l'instant, pas de données supplémentaires nécessaires
        
        // Log du chargement réussi
        echo "\n🎉 FIXTURES COMPLÈTES CHARGÉES AVEC SUCCÈS !\n";
        echo "=====================================\n";
        echo "✅ Utilisateurs: 12 comptes créés (SuperAdmin, Admin, Photographes)\n";
        echo "🏫 Écoles: 9 établissements représentatifs\n";
        echo "📋 Référentiels: Valeurs enum + personnalisées\n";
        echo "📷 Prises de vue: 8 séances complètes\n";
        echo "🎨 Planches: Formats individuels et fratries\n";
        echo "=====================================\n";
        echo "🔑 Comptes de test disponibles:\n";
        echo "   • SuperAdmin: superadmin / SuperAdmin123!\n";
        echo "   • Admin: commercial / Admin123!\n";
        echo "   • Photographe: marie.durand / Photographe123!\n";
        echo "   • Test: test / Test123!\n";
        echo "=====================================\n\n";
    }

    /**
     * Définit l'ordre de chargement des fixtures
     * Pattern: Chain of Responsibility
     */
    public function getDependencies(): array
    {
        return [
            // 1. D'abord les entités de base sans dépendances
            UserFixtures::class,
            EcoleFixtures::class,
            
            // 2. Ensuite les référentiels (enum d'abord, puis personnalisés)
            EnumReferentialFixtures::class,
            ReferentialFixtures::class,
            PlancheFixtures::class,
            
            // 3. Enfin les entités avec relations complexes
            PriseDeVueFixtures::class,
        ];
    }

    /**
     * Groupes de cette fixture pour le chargement sélectif
     */
    public static function getGroups(): array
    {
        return ['main', 'complete', 'test'];
    }
}
