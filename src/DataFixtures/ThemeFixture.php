<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themes = ['Classique', 'Nature', 'Automne', 'Noël', 'Printemps'];
        
        foreach ($themes as $themeName) {
            $theme = new Theme();
            $theme->setNom($themeName);
            $manager->persist($theme);
        }
        
        $manager->flush();
    }
}
