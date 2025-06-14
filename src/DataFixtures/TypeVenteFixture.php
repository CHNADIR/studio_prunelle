<?php

namespace App\DataFixtures;

use App\Entity\TypeVente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeVenteFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = ['Commande directe', 'Commande en ligne', 'Pré-commande', 'Vente sur place'];
        
        foreach ($types as $typeName) {
            $type = new TypeVente();
            $type->setNom($typeName);
            $manager->persist($type);
        }
        
        $manager->flush();
    }
}
