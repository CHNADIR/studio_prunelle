<?php

namespace App\DataFixtures;

use App\Entity\Ecole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
// Si vous avez besoin de référencer des entités d'autres fixtures (ex: un utilisateur par défaut),
// vous pourriez avoir besoin d'implémenter DependentFixtureInterface.
// Pour l'instant, nous allons créer des écoles autonomes.
// use Doctrine\Common\DataFixtures\DependentFixtureInterface;

// Si vous implémentez DependentFixtureInterface, changez la ligne suivante :
// class EcoleFixture extends Fixture implements DependentFixtureInterface
class EcoleFixture extends Fixture
{
    // Constantes pour les références si vous voulez lier ces écoles dans d'autres fixtures
    public const ECOLE_LILAS_REFERENCE = 'ecole-lilas';
    public const ECOLE_ROSES_REFERENCE = 'ecole-roses';
    public const ECOLE_BLEUETS_REFERENCE = 'ecole-bleuets';

    public function load(ObjectManager $manager): void
    {
        $ecolesData = [
            [
                'code' => '75001', // "code école: 5 caractère obligatoire (exemple: 25108)"
                'genre' => 'École Maternelle',
                'nom' => 'École des Lilas',
                'rue' => '1 Rue des Fleurs',
                'ville' => 'Paris',
                'codePostal' => '75001',
                'telephone' => '0123456789',
                'email' => 'contact@ecoledeslilas.fr',
                'active' => true,
                'reference' => self::ECOLE_LILAS_REFERENCE,
            ],
            [
                'code' => '69002',
                'genre' => 'École Primaire',
                'nom' => 'École des Roses',
                'rue' => '15 Avenue des Champs',
                'ville' => 'Lyon',
                'codePostal' => '69002',
                'telephone' => '0456789012',
                'email' => 'direction@ecoledesroses.fr',
                'active' => true,
                'reference' => self::ECOLE_ROSES_REFERENCE,
            ],
            [
                'code' => '33000',
                'genre' => 'Collège',
                'nom' => 'Collège des Bleuets',
                'rue' => '3 Place de la Victoire',
                'ville' => 'Bordeaux',
                'codePostal' => '33000',
                'telephone' => '0567890123',
                'email' => 'secretariat@collegebleuets.com',
                'active' => false, // Exemple d'école inactive
                'reference' => self::ECOLE_BLEUETS_REFERENCE,
            ],
            // Ajoutez d'autres écoles si nécessaire pour vos tests
            [
                'code' => '13001',
                'genre' => 'Lycée Général',
                'nom' => 'Lycée Phocéen',
                'rue' => '10 Canebière',
                'ville' => 'Marseille',
                'codePostal' => '13001',
                'telephone' => '0412345678',
                'email' => 'proviseur@lyceephoceen.fr',
                'active' => true,
                'reference' => 'lycee-phoceen', // Vous pouvez aussi utiliser des chaînes directes pour les références
            ],
        ];

        foreach ($ecolesData as $data) {
            $ecole = new Ecole();
            $ecole->setCode($data['code']);
            $ecole->setGenre($data['genre']);
            $ecole->setNom($data['nom']);
            $ecole->setRue($data['rue']);
            $ecole->setVille($data['ville']);
            $ecole->setCodePostal($data['codePostal']);
            $ecole->setTelephone($data['telephone']);
            $ecole->setEmail($data['email']);
            $ecole->setActive($data['active']);

            $manager->persist($ecole);
            // Ajoute une référence à l'objet Ecole pour pouvoir le réutiliser dans d'autres fixtures
            if (isset($data['reference'])) {
                $this->addReference($data['reference'], $ecole);
            }
        }

        $manager->flush();
    }

    // Si cette fixture dépend d'autres fixtures (par exemple, si une école doit être liée à un utilisateur spécifique)
    // décommentez et remplissez cette méthode.
    /*
    public function getDependencies()
    {
        return [
            UserFixture::class, // Exemple de dépendance
        ];
    }
    */
}