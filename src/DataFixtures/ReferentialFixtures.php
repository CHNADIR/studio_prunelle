<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les référentiels personnalisés (en plus des valeurs enum)
 * Pattern appliqué: Strategy Pattern + Factory
 * Responsabilité: Créer des valeurs personnalisées pour compléter les valeurs enum
 */
class ReferentialFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTypePrisesPersonnalises($manager);
        $this->loadTypeVentesPersonnalises($manager);
        $this->loadThemesPersonnalises($manager);
        
        $manager->flush();
        
        echo "\n✅ RÉFÉRENTIELS PERSONNALISÉS AJOUTÉS :\n";
        echo "   • 6 types de prise supplémentaires\n";
        echo "   • 5 types de vente supplémentaires\n";
        echo "   • 8 thèmes supplémentaires\n";
    }

    /**
     * Charge des types de prise personnalisés (en plus des valeurs enum)
     */
    private function loadTypePrisesPersonnalises(ObjectManager $manager): void
    {
        $typePrisesPersonnalises = [
            [
                'libelle' => 'Séance extérieure',
                'description' => 'Prise de vue en extérieur dans la cour ou le jardin de l\'école'
            ],
            [
                'libelle' => 'Photo 360°',
                'description' => 'Photo panoramique interactive à 360 degrés pour expérience immersive'
            ],
            [
                'libelle' => 'Session artistique',
                'description' => 'Séance photo avec mise en scène artistique et accessoires créatifs'
            ],
            [
                'libelle' => 'Photo remise diplôme',
                'description' => 'Photo officielle pour cérémonie de remise de diplôme ou fin d\'année'
            ],
            [
                'libelle' => 'Séance sport',
                'description' => 'Photos en tenue de sport ou avec équipements sportifs de l\'école'
            ],
            [
                'libelle' => 'Portrait professionnel',
                'description' => 'Portrait de style professionnel pour élèves de terminale ou étudiants'
            ]
        ];

        foreach ($typePrisesPersonnalises as $index => $data) {
            $typePrise = new TypePrise();
            $typePrise->setLibelle($data['libelle']);
            $typePrise->setDescription($data['description']);
            $typePrise->setActive(true);
            $manager->persist($typePrise);
            
            $this->addReference('custom_type_prise_' . $index, $typePrise);
        }
    }

    /**
     * Charge des types de vente personnalisés
     */
    private function loadTypeVentesPersonnalises(ObjectManager $manager): void
    {
        $typeVentesPersonnalises = [
            [
                'libelle' => 'Formule Premium',
                'description' => 'Pack haut de gamme avec retouches professionnelles et formats multiples'
            ],
            [
                'libelle' => 'Commande urgente',
                'description' => 'Traitement accéléré avec livraison express sous 48h'
            ],
            [
                'libelle' => 'Pack souvenir',
                'description' => 'Collection de photos souvenirs de l\'année scolaire avec album personnalisé'
            ],
            [
                'libelle' => 'Vente groupée école',
                'description' => 'Commande groupée organisée par l\'école pour réduction de tarifs'
            ],
            [
                'libelle' => 'Précommande',
                'description' => 'Réservation anticipée avec tarif préférentiel avant la séance'
            ]
        ];

        foreach ($typeVentesPersonnalises as $index => $data) {
            $typeVente = new TypeVente();
            $typeVente->setLibelle($data['libelle']);
            $typeVente->setDescription($data['description']);
            $typeVente->setActive(true);
            $manager->persist($typeVente);
            
            $this->addReference('custom_type_vente_' . $index, $typeVente);
        }
    }

    /**
     * Charge des thèmes personnalisés
     */
    private function loadThemesPersonnalises(ObjectManager $manager): void
    {
        $themesPersonnalises = [
            [
                'libelle' => 'Vintage',
                'description' => 'Thème rétro avec effets vintage, sépia et décors d\'époque'
            ],
            [
                'libelle' => 'Superhéros',
                'description' => 'Thème ludique avec décors de superhéros et accessoires colorés'
            ],
            [
                'libelle' => 'École du futur',
                'description' => 'Thème futuriste avec décors high-tech et éclairages modernes'
            ],
            [
                'libelle' => 'Jungle urbaine',
                'description' => 'Thème naturel avec éléments végétaux et ambiance tropicale'
            ],
            [
                'libelle' => 'Studio professionnel',
                'description' => 'Thème sobre et élégant pour photos d\'identité et officielles'
            ],
            [
                'libelle' => 'Contes et légendes',
                'description' => 'Thème féerique inspiré des contes avec décors magiques'
            ],
            [
                'libelle' => 'Sciences et découvertes',
                'description' => 'Thème éducatif avec instruments scientifiques et laboratoire'
            ],
            [
                'libelle' => 'Arts et créativité',
                'description' => 'Thème artistique avec palettes, pinceaux et œuvres d\'art'
            ]
        ];

        foreach ($themesPersonnalises as $index => $data) {
            $theme = new Theme();
            $theme->setLibelle($data['libelle']);
            $theme->setDescription($data['description']);
            $theme->setActive(true);
            $manager->persist($theme);
            
            $this->addReference('custom_theme_' . $index, $theme);
        }
    }

    /**
     * Groupes de cette fixture
     */
    public static function getGroups(): array
    {
        return ['referential', 'custom'];
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