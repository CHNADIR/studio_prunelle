<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les référentiels TypePrise, TypeVente et Theme
 * Conforme au Sprint 3 - Référentiels dynamiques
 */
class ReferentialFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadTypePrises($manager);
        $this->loadTypeVentes($manager);
        $this->loadThemes($manager);
        
        $manager->flush();
    }

    private function loadTypePrises(ObjectManager $manager): void
    {
        $typePrises = [
            'Photo individuelle',
            'Photo de classe',
            'Photo de groupe',
            'Photo fratrie',
            'Photo panoramique',
            'Portrait scolaire',
            'Photo sport',
            'Photo événement'
        ];

        foreach ($typePrises as $nom) {
            $typePrise = new TypePrise();
            $typePrise->setNom($nom);
            $typePrise->setActive(true);
            $manager->persist($typePrise);
            
            // Créer une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('type_prise_' . strtolower(str_replace(' ', '_', $nom)), $typePrise);
        }
    }

    private function loadTypeVentes(ObjectManager $manager): void
    {
        $typeVentes = [
            'Vente libre',
            'Vente groupée',
            'Prévente',
            'Vente sur commande',
            'Vente directe',
            'Pack famille',
            'Offre spéciale',
            'Vente flash'
        ];

        foreach ($typeVentes as $nom) {
            $typeVente = new TypeVente();
            $typeVente->setNom($nom);
            $typeVente->setActive(true);
            $manager->persist($typeVente);
            
            // Créer une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('type_vente_' . strtolower(str_replace(' ', '_', $nom)), $typeVente);
        }
    }

    private function loadThemes(ObjectManager $manager): void
    {
        $themes = [
            'Automne',
            'Hiver',
            'Printemps',
            'Été',
            'Noël',
            'Pâques',
            'Halloween',
            'Rentrée scolaire',
            'Fin d\'année',
            'Carnaval',
            'Fête des mères',
            'Fête des pères',
            'Saint-Valentin',
            'Thème libre',
            'Studio classique'
        ];

        foreach ($themes as $nom) {
            $theme = new Theme();
            $theme->setNom($nom);
            $theme->setActive(true);
            $manager->persist($theme);
            
            // Créer une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->addReference('theme_' . strtolower(str_replace([' ', '\''], ['_', ''], $nom)), $theme);
        }
    }
} 