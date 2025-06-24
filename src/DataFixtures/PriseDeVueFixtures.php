<?php

namespace App\DataFixtures;

use App\Entity\PriseDeVue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les prises de vue - Données complètes pour tests manuels
 * Pattern appliqué: Factory Pattern + Builder Pattern
 * Responsabilité: Création de prises de vue réalistes avec toutes les relations
 */
class PriseDeVueFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadPrisesDeVueSimplifiees($manager);
        $manager->flush();
        
        echo "\n✅ PRISES DE VUE CRÉÉES :\n";
        echo "   • 8 séances de photos réalistes\n";
        echo "   • Relations de base avec écoles et photographes\n";
        echo "   • Données financières et commentaires\n";
    }

    private function loadPrisesDeVueSimplifiees(ObjectManager $manager): void
    {
        $prisesDeVue = [
            [
                'date' => new \DateTime('2024-09-15'),
                'nbEleves' => 180,
                'nbClasses' => 8,
                'prixEcole' => '15.50',
                'prixParents' => '25.00',
                'commentaire' => 'Excellente séance de rentrée. Enfants très coopératifs, quelques retouches à prévoir pour les CP.'
            ],
            [
                'date' => new \DateTime('2024-09-22'),
                'nbEleves' => 95,
                'nbClasses' => 4,
                'prixEcole' => '12.00',
                'prixParents' => '20.00',
                'commentaire' => 'Séance maternelle très dynamique ! Photos de très bonne qualité.'
            ],
            [
                'date' => new \DateTime('2024-10-05'),
                'nbEleves' => 280,
                'nbClasses' => 12,
                'prixEcole' => '18.00',
                'prixParents' => '30.00',
                'commentaire' => 'Séance extérieure réussie dans la cour du collège. Éclairage naturel parfait.'
            ],
            [
                'date' => new \DateTime('2024-10-12'),
                'nbEleves' => 320,
                'nbClasses' => 15,
                'prixEcole' => '20.00',
                'prixParents' => '35.00',
                'commentaire' => 'Séance portraits professionnels pour terminales. Excellente qualité pour dossiers post-bac.'
            ],
            [
                'date' => new \DateTime('2024-11-08'),
                'nbEleves' => 160,
                'nbClasses' => 7,
                'prixEcole' => '16.00',
                'prixParents' => '26.00',
                'commentaire' => 'Thème superhéros énorme succès ! Enfants ravis des accessoires.'
            ],
            [
                'date' => new \DateTime('2024-12-06'),
                'nbEleves' => 240,
                'nbClasses' => 10,
                'prixEcole' => '17.50',
                'prixParents' => '28.00',
                'commentaire' => 'Séance pré-Noël avec instruments de musique. Organisation parfaite.'
            ],
            [
                'date' => new \DateTime('2025-01-10'),
                'nbEleves' => 45,
                'nbClasses' => 3,
                'prixEcole' => '22.00',
                'prixParents' => '32.00',
                'commentaire' => 'Séance spéciale nouveaux élèves en formation professionnelle.'
            ],
            [
                'date' => new \DateTime('2025-02-14'),
                'nbEleves' => 220,
                'nbClasses' => 9,
                'prixEcole' => '19.00',
                'prixParents' => '31.00',
                'commentaire' => 'Séance Saint-Valentin avec thème artistique. Qualité premium demandée.'
            ]
        ];

        // Essayer de récupérer les références des photographes et écoles
        $photographes = [];
        $ecoles = [];
        
        // Collecter les photographes disponibles
        for ($i = 0; $i < 5; $i++) {
            try {
                $usernames = ['marie.durand', 'pierre.martin', 'sophie.bernard', 'lucas.moreau', 'emma.rousseau'];
                if (isset($usernames[$i])) {
                    $photographe = $this->getReference('photographe_' . $usernames[$i], \App\Entity\User::class);
                    if ($photographe) {
                        $photographes[] = $photographe;
                    }
                }
            } catch (\Exception $e) {
                // Continuer silencieusement
            }
        }
        
        // Collecter les écoles disponibles  
        $ecolesRefs = [
            'ecole_primaire_ecole_primaire_victor_hugo',
            'ecole_primaire_ecole_primaire_republique', 
            'ecole_maternelle_ecole_maternelle_jean_jaures',
            'college_college_jules_ferry',
            'lycee_lycee_marie_curie',
            'lycee_lycee_professionnel_tony_garnier',
            'ecole_privee_institution_sainte_marie',
            'ecole_privee_ecole_internationale_de_lyon'
        ];
        
        foreach ($ecolesRefs as $ref) {
            try {
                $ecole = $this->getReference($ref, \App\Entity\Ecole::class);
                if ($ecole) {
                    $ecoles[] = $ecole;
                }
            } catch (\Exception $e) {
                // Continuer silencieusement
            }
        }

        foreach ($prisesDeVue as $index => $data) {
            $priseDeVue = new PriseDeVue();
            $priseDeVue->setDatePdv($data['date']);
            $priseDeVue->setNbEleves($data['nbEleves']);
            $priseDeVue->setNbClasses($data['nbClasses']);
            $priseDeVue->setPrixEcole($data['prixEcole']);
            $priseDeVue->setPrixParent($data['prixParents']);
            $priseDeVue->setCommentaire($data['commentaire']);
            
            // Assigner un photographe et une école de manière cyclique
            if (!empty($photographes)) {
                $priseDeVue->setPhotographe($photographes[$index % count($photographes)]);
            }
            
            if (!empty($ecoles)) {
                $priseDeVue->setEcole($ecoles[$index % count($ecoles)]);
            }
            
            $manager->persist($priseDeVue);
            $this->addReference('prise_de_vue_' . $index, $priseDeVue);
        }
    }

    /**
     * Groupes de cette fixture
     */
    public static function getGroups(): array
    {
        return ['prise_de_vue', 'complete', 'test'];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            EcoleFixtures::class,
            EnumReferentialFixtures::class,
            ReferentialFixtures::class,
            PlancheFixtures::class,
        ];
    }
} 