<?php

namespace App\DataFixtures;

use App\Entity\Planche;
use App\Enum\PlancheUsage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les planches - Sprint 4
 * Conforme aux exigences du PRD pour les planches
 */
class PlancheFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadPlanchesIndividuelles($manager);
        $this->loadPlanchesFratries($manager);
        $this->loadPlanchesSeules($manager);
        
        $manager->flush();
    }

    private function loadPlanchesIndividuelles(ObjectManager $manager): void
    {
        $planchesIndividuelles = [
            [
                'nom' => 'Portrait A4 Individuel',
                'formats' => ['21x29.7', 'A4', 'portrait'],
                'prixEcole' => '8.50',
                'prixParents' => '12.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Photo 10x15 Individuelle',
                'formats' => ['10x15', 'format standard'],
                'prixEcole' => '3.50',
                'prixParents' => '6.00',
                'usage' => 'INCLUSE'
            ],
            [
                'nom' => 'Portrait 13x18 Individuel',
                'formats' => ['13x18', 'portrait moyen'],
                'prixEcole' => '5.50',
                'prixParents' => '9.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Magnets Individuels',
                'formats' => ['magnet 7x5', 'magnet rond'],
                'prixEcole' => '2.50',
                'prixParents' => '4.50',
                'usage' => 'INCLUSE'
            ],
            [
                'nom' => 'Badge Photo Individuel',
                'formats' => ['badge 5x5', 'badge rond'],
                'prixEcole' => '1.50',
                'prixParents' => '3.00',
                'usage' => 'INCLUSE'
            ]
        ];

        foreach ($planchesIndividuelles as $index => $data) {
            $planche = new Planche();
            $planche->setNom($data['nom']);
            $planche->setType(PlancheUsage::INDIVIDUELLE);
            $planche->setUsage($data['usage']);
            $planche->setFormats($data['formats']);
            $planche->setPrixEcole($data['prixEcole']);
            $planche->setPrixParents($data['prixParents']);
            $planche->setActif(true);
            
            $manager->persist($planche);
            $this->addReference('planche_individuelle_' . $index, $planche);
        }
    }

    private function loadPlanchesFratries(ObjectManager $manager): void
    {
        $planchesFratries = [
            [
                'nom' => 'Pack Fratrie A4',
                'formats' => ['21x29.7', 'A4', '2-3 enfants'],
                'prixEcole' => '15.50',
                'prixParents' => '22.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Photo Fratrie 13x18',
                'formats' => ['13x18', 'fratrie 2-4 enfants'],
                'prixEcole' => '9.50',
                'prixParents' => '15.00',
                'usage' => 'INCLUSE'
            ],
            [
                'nom' => 'Collage Fratrie 10x15',
                'formats' => ['10x15', 'collage multiple'],
                'prixEcole' => '6.50',
                'prixParents' => '11.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Calendrier Fratrie',
                'formats' => ['A4', 'calendrier personnalisé'],
                'prixEcole' => '12.00',
                'prixParents' => '18.00',
                'usage' => 'SEULE'
            ]
        ];

        foreach ($planchesFratries as $index => $data) {
            $planche = new Planche();
            $planche->setNom($data['nom']);
            $planche->setType(PlancheUsage::FRATRIE);
            $planche->setUsage($data['usage']);
            $planche->setFormats($data['formats']);
            $planche->setPrixEcole($data['prixEcole']);
            $planche->setPrixParents($data['prixParents']);
            $planche->setActif(true);
            
            $manager->persist($planche);
            $this->addReference('planche_fratrie_' . $index, $planche);
        }
    }

    private function loadPlanchesSeules(ObjectManager $manager): void
    {
        $planchesSeules = [
            [
                'nom' => 'Poster A3 Seul',
                'formats' => ['A3', '29.7x42', 'poster grand format'],
                'prixEcole' => '18.00',
                'prixParents' => '25.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Canvas 20x30',
                'formats' => ['20x30', 'toile canvas'],
                'prixEcole' => '24.00',
                'prixParents' => '35.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Livre Photo A5',
                'formats' => ['A5', 'livre 20 pages'],
                'prixEcole' => '16.00',
                'prixParents' => '28.00',
                'usage' => 'SEULE'
            ],
            [
                'nom' => 'Mug Personnalisé',
                'formats' => ['mug standard', 'céramique'],
                'prixEcole' => '8.00',
                'prixParents' => '14.00',
                'usage' => 'SEULE'
            ]
        ];

        foreach ($planchesSeules as $index => $data) {
            $planche = new Planche();
            $planche->setNom($data['nom']);
            $planche->setType(PlancheUsage::SEULE);
            $planche->setUsage($data['usage']);
            $planche->setFormats($data['formats']);
            $planche->setPrixEcole($data['prixEcole']);
            $planche->setPrixParents($data['prixParents']);
            $planche->setActif(true);
            
            $manager->persist($planche);
            $this->addReference('planche_seule_' . $index, $planche);
        }
    }

    public function getDependencies(): array
    {
        return [
            // Les planches peuvent être créées indépendamment
        ];
    }
} 