<?php

namespace App\DataFixtures;

use App\Entity\Ecole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les écoles - Système complet
 * Pattern appliqué: Factory Pattern + Builder Pattern
 * Responsabilité: Création d'écoles représentatives pour tests complets
 */
class EcoleFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadEcolesPrimaires($manager);
        $this->loadEcolesMaternelles($manager);
        $this->loadColleges($manager);
        $this->loadLycees($manager);
        $this->loadEcolesPrivees($manager);
        
        $manager->flush();
    }

    /**
     * Charge les écoles primaires
     */
    private function loadEcolesPrimaires(ObjectManager $manager): void
    {
        $ecolesPrimaires = [
            [
                'code' => '69001',
                'nom' => 'École Primaire Victor Hugo',
                'adresse' => '15 rue Victor Hugo',
                'ville' => 'Lyon',
                'codePostal' => '69001',
                'telephone' => '04.72.10.20.30',
                'email' => 'direction@victor-hugo-lyon.fr',
                'typeEtablissement' => 'École primaire publique',
                'active' => true
            ],
            [
                'code' => '69101',
                'nom' => 'École Primaire République',
                'adresse' => '42 avenue de la République',
                'ville' => 'Villeurbanne',
                'codePostal' => '69100',
                'telephone' => '04.78.15.25.35',
                'email' => 'ecole.republique@villeurbanne.fr',
                'typeEtablissement' => 'École primaire publique',
                'active' => true
            ],
            [
                'code' => '69301',
                'nom' => 'École Élémentaire Les Tilleuls',
                'adresse' => '8 impasse des Tilleuls',
                'ville' => 'Caluire-et-Cuire',
                'codePostal' => '69300',
                'telephone' => '04.72.27.37.47',
                'email' => 'tilleuls@caluire.fr',
                'typeEtablissement' => 'École primaire publique',
                'active' => true
            ]
        ];

        foreach ($ecolesPrimaires as $index => $data) {
            $ecole = $this->createEcole($data);
            $manager->persist($ecole);
            $this->addReference('ecole_primaire_' . $this->slugify($data['nom']), $ecole);
        }
    }

    /**
     * Charge les écoles maternelles
     */
    private function loadEcolesMaternelles(ObjectManager $manager): void
    {
        $ecolesMaternelles = [
            [
                'code' => '69031',
                'nom' => 'École Maternelle Jean Jaurès',
                'adresse' => '23 rue Jean Jaurès',
                'ville' => 'Lyon',
                'codePostal' => '69003',
                'telephone' => '04.72.36.46.56',
                'email' => 'maternelle.jaures@lyon.fr',
                'typeEtablissement' => 'École maternelle publique',
                'active' => true
            ],
            [
                'code' => '69501',
                'nom' => 'École Maternelle Les Petits Princes',
                'adresse' => '7 allée des Princes',
                'ville' => 'Bron',
                'codePostal' => '69500',
                'telephone' => '04.72.81.91.01',
                'email' => 'petits.princes@bron.fr',
                'typeEtablissement' => 'École maternelle publique',
                'active' => true
            ]
        ];

        foreach ($ecolesMaternelles as $index => $data) {
            $ecole = $this->createEcole($data);
            $manager->persist($ecole);
            $this->addReference('ecole_maternelle_' . $this->slugify($data['nom']), $ecole);
        }
    }

    /**
     * Charge les collèges
     */
    private function loadColleges(ObjectManager $manager): void
    {
        $colleges = [
            [
                'code' => '69061',
                'nom' => 'Collège Jules Ferry',
                'adresse' => '50 boulevard Jules Ferry',
                'ville' => 'Lyon',
                'codePostal' => '69006',
                'telephone' => '04.72.44.54.64',
                'email' => 'secretariat@jules-ferry-lyon.fr',
                'typeEtablissement' => 'Collège public',
                'active' => true
            ],
            [
                'code' => '69021',
                'nom' => 'Collège Ampère',
                'adresse' => '12 rue André-Marie Ampère',
                'ville' => 'Lyon',
                'codePostal' => '69002',
                'telephone' => '04.72.41.51.61',
                'email' => 'college.ampere@lyon.fr',
                'typeEtablissement' => 'Collège public',
                'active' => true
            ]
        ];

        foreach ($colleges as $index => $data) {
            $ecole = $this->createEcole($data);
            $manager->persist($ecole);
            $this->addReference('college_' . $this->slugify($data['nom']), $ecole);
        }
    }

    /**
     * Charge les lycées
     */
    private function loadLycees(ObjectManager $manager): void
    {
        $lycees = [
            [
                'code' => '69081',
                'nom' => 'Lycée Marie Curie',
                'adresse' => '85 avenue Marie Curie',
                'ville' => 'Lyon',
                'codePostal' => '69008',
                'telephone' => '04.72.78.88.98',
                'email' => 'lycee.curie@rhone.fr',
                'typeEtablissement' => 'Lycée général et technologique',
                'active' => true
            ],
            [
                'code' => '69071',
                'nom' => 'Lycée Professionnel Tony Garnier',
                'adresse' => '20 rue Tony Garnier',
                'ville' => 'Lyon',
                'codePostal' => '69007',
                'telephone' => '04.72.73.83.93',
                'email' => 'lp.garnier@rhone.fr',
                'typeEtablissement' => 'Lycée professionnel',
                'active' => true
            ]
        ];

        foreach ($lycees as $index => $data) {
            $ecole = $this->createEcole($data);
            $manager->persist($ecole);
            $this->addReference('lycee_' . $this->slugify($data['nom']), $ecole);
        }
    }

    /**
     * Charge les écoles privées
     */
    private function loadEcolesPrivees(ObjectManager $manager): void
    {
        $ecolesPrivees = [
            [
                'code' => '69051',
                'nom' => 'Institution Sainte-Marie',
                'adresse' => '35 rue Sainte-Marie',
                'ville' => 'Lyon',
                'codePostal' => '69005',
                'telephone' => '04.72.56.66.76',
                'email' => 'direction@sainte-marie-lyon.fr',
                'typeEtablissement' => 'École privée catholique',
                'active' => true
            ],
            [
                'code' => '69091',
                'nom' => 'École Internationale de Lyon',
                'adresse' => '80 chemin du Bachut',
                'ville' => 'Lyon',
                'codePostal' => '69009',
                'telephone' => '04.72.10.20.30',
                'email' => 'admissions@eil-lyon.org',
                'typeEtablissement' => 'École internationale privée',
                'active' => true
            ]
        ];

        foreach ($ecolesPrivees as $index => $data) {
            $ecole = $this->createEcole($data);
            $manager->persist($ecole);
            $this->addReference('ecole_privee_' . $this->slugify($data['nom']), $ecole);
        }
    }

    /**
     * Factory method pour créer une école
     * Pattern: Factory Method
     */
    private function createEcole(array $data): Ecole
    {
        $ecole = new Ecole();
        $ecole->setCode($data['code']);
        $ecole->setNom($data['nom']);
        $ecole->setGenre($data['typeEtablissement']);
        $ecole->setAdresse($data['adresse']);
        $ecole->setVille($data['ville']);
        $ecole->setCodePostal($data['codePostal']);
        $ecole->setTelephone($data['telephone']);
        $ecole->setEmail($data['email']);
        $ecole->setActive($data['active']);
        
        return $ecole;
    }

    /**
     * Génère un code école unique (5 chiffres)
     */
    private function generateCodeEcole(array $data): string
    {
        $base = substr($data['codePostal'], 0, 2);
        $unique = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        return $base . $unique;
    }

    /**
     * Convertit un nom en slug pour les références
     */
    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim($text, '_');
        return $text;
    }

    /**
     * Groupe de cette fixture
     */
    public static function getGroups(): array
    {
        return ['ecole', 'core'];
    }

    /**
     * Pas de dépendances - les écoles sont créées indépendamment
     */
    public function getDependencies(): array
    {
        return [];
    }
} 