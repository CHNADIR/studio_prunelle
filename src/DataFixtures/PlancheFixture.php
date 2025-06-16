<?php

namespace App\DataFixtures;

use App\Entity\Planche;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlancheFixture extends Fixture
{
    public const PLANCHE_A_REFERENCE = 'planche-a'; // Format 13x18 + 2 petits
    public const PLANCHE_B_REFERENCE = 'planche-b'; // Format 15x21 + 4 petits
    public const PLANCHE_C_REFERENCE = 'planche-c'; // Format 18x24 + 6 petits
    public const PLANCHE_FRATRIE1_REFERENCE = 'planche-fratrie1'; // 2 photos 13x18
    public const PLANCHE_FRATRIE2_REFERENCE = 'planche-fratrie2'; // 1 photo 18x24
    public const PLANCHE_GROUPE_CLASSE_REFERENCE = 'planche-groupe-classe'; // Nouvelle constante

    public function load(ObjectManager $manager): void
    {
        $planchesData = [
            ['nom' => 'Format 13x18 + 2 petits', 'categorie' => Planche::CATEGORIE_INDIVIDUEL, 'reference' => self::PLANCHE_A_REFERENCE],
            ['nom' => 'Format 15x21 + 4 petits', 'categorie' => Planche::CATEGORIE_INDIVIDUEL, 'reference' => self::PLANCHE_B_REFERENCE],
            ['nom' => 'Format 18x24 + 6 petits', 'categorie' => Planche::CATEGORIE_INDIVIDUEL, 'reference' => self::PLANCHE_C_REFERENCE],
            ['nom' => '2 photos 13x18', 'categorie' => Planche::CATEGORIE_FRATRIE, 'reference' => self::PLANCHE_FRATRIE1_REFERENCE],
            ['nom' => '1 photo 18x24', 'categorie' => Planche::CATEGORIE_FRATRIE, 'reference' => self::PLANCHE_FRATRIE2_REFERENCE],
            ['nom' => 'Photo de groupe classe standard', 'categorie' => Planche::CATEGORIE_GROUPE_CLASSE, 'reference' => self::PLANCHE_GROUPE_CLASSE_REFERENCE], // Ajout
        ];
        
        foreach ($planchesData as $data) {
            $planche = new Planche();
            $planche->setNom($data['nom']);
            $planche->setCategorie($data['categorie']);
            // Vous pouvez ajouter une description et une image par défaut si nécessaire pour les tests
            // $planche->setDescriptionContenu('Description de test pour ' . $data['nom']);
            // $planche->setImageFilename('default_planche.jpg'); // Si vous avez une image par défaut
            $manager->persist($planche);
            $this->addReference($data['reference'], $planche);
        }
        
        $manager->flush();
    }
}
