<?php

namespace App\DataFixtures;

use App\Entity\Planche;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les planches personnalisées (en plus des valeurs enum)
 * Pattern appliqué: Factory Pattern + Strategy
 * Responsabilité: Créer des planches personnalisées pour compléter les valeurs enum
 */
class PlancheFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadPlanchesPersonnalisees($manager);
        $manager->flush();
        
        echo "\n✅ PLANCHES PERSONNALISÉES AJOUTÉES :\n";
        echo "   • 15 formats de planches supplémentaires\n";
        echo "   • Catégories : Premium, Magnétique, Professionnel, Créatif\n";
    }

    /**
     * Charge des planches personnalisées (en plus des valeurs enum)
     */
    private function loadPlanchesPersonnalisees(ObjectManager $manager): void
    {
        $planchesPersonnalisees = [
            // Catégorie Premium
            [
                'libelle' => 'Pochette premium mat',
                'description' => 'Pochette de qualité supérieure avec finition mate anti-reflets et protection UV'
            ],
            [
                'libelle' => 'Pochette premium brillante',
                'description' => 'Pochette de qualité supérieure avec finition brillante haute définition'
            ],
            [
                'libelle' => 'Album cuir personnalisé',
                'description' => 'Album de luxe avec couverture en cuir véritable et gravure personnalisée'
            ],
            
            // Catégorie Magnétique
            [
                'libelle' => 'Support magnétique carré',
                'description' => 'Photo format carré avec support magnétique pour frigo ou tableau métallique'
            ],
            [
                'libelle' => 'Support magnétique rond',
                'description' => 'Photo format rond avec support magnétique décoratif'
            ],
            [
                'libelle' => 'Magnet photo souvenir',
                'description' => 'Magnet personnalisé avec photo et texte souvenir de l\'année'
            ],
            
            // Catégorie Professionnelle
            [
                'libelle' => 'Badge photo officiel',
                'description' => 'Photo format badge pour carnet de correspondance ou carte étudiant'
            ],
            [
                'libelle' => 'Portrait format CV',
                'description' => 'Photo format portrait professionnel pour CV et démarches administratives'
            ],
            [
                'libelle' => 'Photo d\'identité scolaire',
                'description' => 'Format réglementaire pour documents officiels de l\'établissement'
            ],
            
            // Catégorie Créative
            [
                'libelle' => 'Poster classe A3',
                'description' => 'Photo de classe format poster A3 pour affichage mural en classe'
            ],
            [
                'libelle' => 'Planche contact miniatures',
                'description' => 'Planche avec miniatures de toutes les photos de la séance pour prévisualisation'
            ],
            [
                'libelle' => 'Cadre bois naturel',
                'description' => 'Photo encadrée dans un cadre en bois naturel avec passe-partout'
            ],
            
            // Formats spéciaux
            [
                'libelle' => 'Format paysage panoramique',
                'description' => 'Présentation en format paysage panoramique pour photos de groupe étendues'
            ],
            [
                'libelle' => 'Triptyque artistique',
                'description' => 'Présentation en trois volets pour mise en scène créative'
            ],
            [
                'libelle' => 'Album souvenir numérique',
                'description' => 'Album personnalisé avec QR code pour accès aux photos numériques haute résolution'
            ]
        ];

        foreach ($planchesPersonnalisees as $index => $data) {
            $planche = new Planche();
            $planche->setLibelle($data['libelle']);
            $planche->setDescription($data['description']);
            $planche->setActive(true);
            
            $manager->persist($planche);
            $this->addReference('custom_planche_' . $index, $planche);
        }
    }

    /**
     * Groupes de cette fixture
     */
    public static function getGroups(): array
    {
        return ['planches', 'custom'];
    }

    /**
     * Dépendances - doit être chargé après les valeurs enum
     */
    public function getDependencies(): array
    {
        return [
            EnumReferentialFixtures::class,
        ];
    }
} 
