<?php

namespace App\DataFixtures;

use App\Entity\TypeVente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeVenteFixture extends Fixture
{
    public const TYPE_VENTE_DIRECTE_REFERENCE = 'type-vente-directe';
    public const TYPE_VENTE_INTERNET_REFERENCE = 'type-vente-internet';
    public const TYPE_VENTE_PRECOMMANDE_REFERENCE = 'type-vente-precommande';
    public const TYPE_VENTE_BON_COMMANDE_REFERENCE = 'type-vente-bon-commande'; // Modifié pour correspondre à PriseDeVueFixture

    public function load(ObjectManager $manager): void
    {
        $typesData = [
            ['nom' => 'Commande directe', 'reference' => self::TYPE_VENTE_DIRECTE_REFERENCE],
            ['nom' => 'Commande en ligne', 'reference' => self::TYPE_VENTE_INTERNET_REFERENCE], // Correspond à TYPE_VENTE_INTERNET_REFERENCE
            ['nom' => 'Pré-commande', 'reference' => self::TYPE_VENTE_PRECOMMANDE_REFERENCE],
            ['nom' => 'Vente sur bon de commande', 'reference' => self::TYPE_VENTE_BON_COMMANDE_REFERENCE], // Nom plus explicite
        ];
        
        foreach ($typesData as $data) {
            $type = new TypeVente();
            $type->setNom($data['nom']);
            $manager->persist($type);
            $this->addReference($data['reference'], $type);
        }
        
        $manager->flush();
    }
}
