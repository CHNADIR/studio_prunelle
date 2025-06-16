<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixture extends Fixture
{
    public const THEME_CLASSIQUE_REFERENCE = 'theme-classique';
    public const THEME_NATURE_REFERENCE = 'theme-nature';
    public const THEME_AUTOMNE_REFERENCE = 'theme-automne';
    public const THEME_NOEL_REFERENCE = 'theme-noel';
    public const THEME_PRINTEMPS_REFERENCE = 'theme-printemps';

    public function load(ObjectManager $manager): void
    {
        $themesData = [
            ['nom' => 'Classique', 'reference' => self::THEME_CLASSIQUE_REFERENCE],
            ['nom' => 'Nature', 'reference' => self::THEME_NATURE_REFERENCE],
            ['nom' => 'Automne', 'reference' => self::THEME_AUTOMNE_REFERENCE],
            ['nom' => 'Noël', 'reference' => self::THEME_NOEL_REFERENCE],
            ['nom' => 'Printemps', 'reference' => self::THEME_PRINTEMPS_REFERENCE],
        ];
        
        foreach ($themesData as $data) {
            $theme = new Theme();
            $theme->setNom($data['nom']);
            $manager->persist($theme);
            $this->addReference($data['reference'], $theme);
        }
        
        $manager->flush();
    }
}
