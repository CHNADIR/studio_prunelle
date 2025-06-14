<?php

namespace App\DataFixtures;

use App\Entity\TypePrise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypePriseFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = ['Portrait individuel', 'Photo de classe', 'Photo de groupe', 'Portrait fratrie'];
        
        foreach ($types as $typeName) {
            $type = new TypePrise();
            $type->setNom($typeName);
            $manager->persist($type);
        }
        
        $manager->flush();
    }
}
