<?php

namespace App\DataFixtures;

use App\Entity\TypePrise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypePriseFixture extends Fixture
{
    public const TYPE_PRISE_INDIVIDUEL_REFERENCE = 'type-prise-individuel';
    public const TYPE_PRISE_CLASSE_REFERENCE = 'type-prise-classe';
    public const TYPE_PRISE_GROUPE_REFERENCE = 'type-prise-groupe';
    public const TYPE_PRISE_FRATRIE_REFERENCE = 'type-prise-fratrie';

    public function load(ObjectManager $manager): void
    {
        $typesData = [
            ['nom' => 'Portrait individuel', 'reference' => self::TYPE_PRISE_INDIVIDUEL_REFERENCE],
            ['nom' => 'Photo de classe', 'reference' => self::TYPE_PRISE_CLASSE_REFERENCE],
            ['nom' => 'Photo de groupe', 'reference' => self::TYPE_PRISE_GROUPE_REFERENCE],
            ['nom' => 'Portrait fratrie', 'reference' => self::TYPE_PRISE_FRATRIE_REFERENCE],
        ];
        
        foreach ($typesData as $data) {
            $type = new TypePrise();
            $type->setNom($data['nom']);
            $manager->persist($type);
            $this->addReference($data['reference'], $type);
        }
        
        $manager->flush();
    }
}
