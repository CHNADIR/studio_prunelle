<?php

namespace App\DataFixtures;

use App\Entity\PriseDeVue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les prises de vue - Sprint 5
 * Conforme aux exigences du PRD pour les prises de vue
 */
class PriseDeVueFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadPrisesDeVue($manager);
        $manager->flush();
    }

    private function loadPrisesDeVue(ObjectManager $manager): void
    {
        $prisesDeVue = [
            [
                'date' => new \DateTime('2024-09-15'),
                'ecole' => 'ecole_primaire_victor_hugo',
                'photographe' => 'photographe_marie_durand',
                'nbEleves' => 180,
                'nbClasses' => 8,
                'classes' => 'CP, CE1, CE2, CM1, CM2',
                'typePrise' => 'type_prise_photo_individuelle',
                'typeVente' => 'type_vente_vente_libre',
                'theme' => 'theme_automne',
                'prixEcole' => '15.50',
                'prixParents' => '25.00',
                'planchesIndividuelles' => ['planche_individuelle_0', 'planche_individuelle_1'],
                'planchesFratries' => ['planche_fratrie_0'],
                'commentaire' => 'Excellente séance, enfants très coopératifs. Quelques retouches à prévoir pour les CP.'
            ],
            [
                'date' => new \DateTime('2024-10-05'),
                'ecole' => 'ecole_maternelle_jean_jaures',
                'photographe' => 'photographe_pierre_martin',
                'nbEleves' => 120,
                'nbClasses' => 5,
                'classes' => 'PS, MS, GS',
                'typePrise' => 'type_prise_photo_de_classe',
                'typeVente' => 'type_vente_vente_groupee',
                'theme' => 'theme_halloween',
                'prixEcole' => '12.00',
                'prixParents' => '20.00',
                'planchesIndividuelles' => ['planche_individuelle_2', 'planche_individuelle_3'],
                'planchesFratries' => ['planche_fratrie_1', 'planche_fratrie_2'],
                'commentaire' => 'Séance avec les maternelles, très énergiques ! Photos de groupe réussies.'
            ],
            [
                'date' => new \DateTime('2024-10-20'),
                'ecole' => 'college_jules_ferry',
                'photographe' => 'photographe_marie_durand',
                'nbEleves' => 450,
                'nbClasses' => 18,
                'classes' => '6e, 5e, 4e, 3e',
                'typePrise' => 'type_prise_portrait_scolaire',
                'typeVente' => 'type_vente_vente_libre',
                'theme' => 'theme_classique',
                'prixEcole' => '18.00',
                'prixParents' => '28.00',
                'planchesIndividuelles' => ['planche_individuelle_0', 'planche_individuelle_4'],
                'planchesFratries' => [],
                'commentaire' => 'Collégiens plus difficiles à gérer mais résultats satisfaisants. Bon éclairage.'
            ],
            [
                'date' => new \DateTime('2024-11-12'),
                'ecole' => 'lycee_marie_curie',
                'photographe' => 'photographe_pierre_martin',
                'nbEleves' => 280,
                'nbClasses' => 12,
                'classes' => '2nde, 1ère, Terminale',
                'typePrise' => 'type_prise_photo_de_groupe',
                'typeVente' => 'type_vente_pack_famille',
                'theme' => 'theme_moderne',
                'prixEcole' => '22.00',
                'prixParents' => '35.00',
                'planchesIndividuelles' => ['planche_individuelle_1', 'planche_individuelle_2'],
                'planchesFratries' => ['planche_fratrie_3'],
                'commentaire' => 'Lycéens coopératifs, très bon rendu. Commande spéciale pour le yearbook.'
            ],
            [
                'date' => new \DateTime('2024-12-01'),
                'ecole' => 'ecole_primaire_republique',
                'photographe' => 'photographe_sophie_bernard',
                'nbEleves' => 200,
                'nbClasses' => 9,
                'classes' => 'CP, CE1, CE2, CM1, CM2, ULIS',
                'typePrise' => 'type_prise_photo_evenement',
                'typeVente' => 'type_vente_vente_libre',
                'theme' => 'theme_noel',
                'prixEcole' => '16.50',
                'prixParents' => '26.00',
                'planchesIndividuelles' => ['planche_individuelle_3', 'planche_individuelle_4'],
                'planchesFratries' => ['planche_fratrie_0', 'planche_fratrie_1'],
                'commentaire' => 'Séance de Noël très réussie. Décor festif apprécié par tous.'
            ],
            [
                'date' => new \DateTime('2024-01-15'),
                'ecole' => 'ecole_primaire_victor_hugo',
                'photographe' => 'photographe_marie_durand',
                'nbEleves' => 160,
                'nbClasses' => 7,
                'classes' => 'CP, CE1, CE2, CM1, CM2',
                'typePrise' => 'type_prise_photo_fratrie',
                'typeVente' => 'type_vente_pack_famille',
                'theme' => 'theme_hiver',
                'prixEcole' => '14.00',
                'prixParents' => '24.00',
                'planchesIndividuelles' => ['planche_individuelle_0'],
                'planchesFratries' => ['planche_fratrie_2', 'planche_fratrie_3'],
                'commentaire' => 'Session fratries très demandée. Belles photos de famille.'
            ]
        ];

        foreach ($prisesDeVue as $index => $data) {
            $priseDeVue = new PriseDeVue();
            $priseDeVue->setDate($data['date']);
            $priseDeVue->setNbEleves($data['nbEleves']);
            $priseDeVue->setNbClasses($data['nbClasses']);
            $priseDeVue->setClasses($data['classes']);
            $priseDeVue->setPrixEcole($data['prixEcole']);
            $priseDeVue->setPrixParents($data['prixParents']);
            $priseDeVue->setCommentaire($data['commentaire']);
            
            // Relations via références
            if ($this->hasReference($data['ecole'])) {
                $priseDeVue->setEcole($this->getReference($data['ecole']));
            }
            
            if ($this->hasReference($data['photographe'])) {
                $priseDeVue->setPhotographe($this->getReference($data['photographe']));
            }
            
            if ($this->hasReference($data['typePrise'])) {
                $priseDeVue->setTypePrise($this->getReference($data['typePrise']));
            }
            
            if ($this->hasReference($data['typeVente'])) {
                $priseDeVue->setTypeVente($this->getReference($data['typeVente']));
            }
            
            if ($this->hasReference($data['theme'])) {
                $priseDeVue->setTheme($this->getReference($data['theme']));
            }
            
            // Planches individuelles
            foreach ($data['planchesIndividuelles'] as $plancheRef) {
                if ($this->hasReference($plancheRef)) {
                    $priseDeVue->addPlancheIndividuelle($this->getReference($plancheRef));
                }
            }
            
            // Planches fratries
            foreach ($data['planchesFratries'] as $plancheRef) {
                if ($this->hasReference($plancheRef)) {
                    $priseDeVue->addPlancheFratrie($this->getReference($plancheRef));
                }
            }
            
            $manager->persist($priseDeVue);
            $this->addReference('prise_de_vue_' . $index, $priseDeVue);
        }
    }

    public function getDependencies(): array
    {
        return [
            ReferentialFixtures::class,
            PlancheFixtures::class,
            // UserFixtures::class, // Si les fixtures utilisateurs existent
            // EcoleFixtures::class, // Si les fixtures écoles existent
        ];
    }
} 