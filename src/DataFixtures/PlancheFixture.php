<?php

namespace App\DataFixtures;

use App\Entity\Planche;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlancheFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $planches = [
            ['Format 13x18 + 2 petits', 'individuel'],
            ['Format 15x21 + 4 petits', 'individuel'],
            ['Format 18x24 + 6 petits', 'individuel'],
            ['2 photos 13x18', 'fratrie'],
            ['1 photo 18x24', 'fratrie']
        ];
        
        foreach ($planches as [$nom, $categorie]) {
            $planche = new Planche();
            $planche->setNom($nom);
            $planche->setCategorie($categorie);
            $manager->persist($planche);
        }
        
        $manager->flush();
    }
}
