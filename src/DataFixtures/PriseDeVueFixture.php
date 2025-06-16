<?php

namespace App\DataFixtures;

use App\Entity\PriseDeVue;
use App\Entity\User; // Assurez-vous que l'entité User est importée
use App\Entity\Ecole; // Assurez-vous que l'entité Ecole est importée
use App\Entity\Theme; // Assurez-vous que l'entité Theme est importée
use App\Entity\TypePrise; // Assurez-vous que l'entité TypePrise est importée
use App\Entity\TypeVente; // Assurez-vous que l'entité TypeVente est importée
use App\Entity\Planche; // Assurez-vous que l'entité Planche est importée
use App\DataFixtures\UserFixture;
use App\DataFixtures\EcoleFixture;
use App\DataFixtures\ThemeFixture;
use App\DataFixtures\TypePriseFixture;
use App\DataFixtures\TypeVenteFixture;
use App\DataFixtures\PlancheFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory; // Importation de Faker

class PriseDeVueFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer les références des autres fixtures
        /** @var User $photographe1 */
        $photographe1 = $this->getReference(UserFixture::PHOTOGRAPHE1_USER_REFERENCE, User::class);
        /** @var User $photographe2 */
        $photographe2 = $this->getReference(UserFixture::PHOTOGRAPHE2_USER_REFERENCE, User::class);
        // $respAdmin = $this->getReference(UserFixture::RESP_ADMIN_USER_REFERENCE, User::class); // Décommentez si utilisé

        /** @var Ecole $ecoleLilas */
        $ecoleLilas = $this->getReference(EcoleFixture::ECOLE_LILAS_REFERENCE, Ecole::class);
        /** @var Ecole $ecoleRoses */
        $ecoleRoses = $this->getReference(EcoleFixture::ECOLE_ROSES_REFERENCE, Ecole::class);

        /** @var Theme $themeNoel */
        $themeNoel = $this->getReference(ThemeFixture::THEME_NOEL_REFERENCE, Theme::class);
        /** @var Theme $themePrintemps */
        // $themePrintemps = $this->getReference(ThemeFixture::THEME_PRINTEMPS_REFERENCE, Theme::class); // Décommentez si utilisé

        /** @var TypePrise $typePriseIndividuel */
        $typePriseIndividuel = $this->getReference(TypePriseFixture::TYPE_PRISE_INDIVIDUEL_REFERENCE, TypePrise::class);
        /** @var TypePrise $typePriseGroupe */
        // $typePriseGroupe = $this->getReference(TypePriseFixture::TYPE_PRISE_GROUPE_REFERENCE, TypePrise::class); // Décommentez si utilisé

        /** @var TypeVente $typeVenteInternet */
        $typeVenteInternet = $this->getReference(TypeVenteFixture::TYPE_VENTE_INTERNET_REFERENCE, TypeVente::class);
        /** @var TypeVente $typeVenteBonCommande */
        // $typeVenteBonCommande = $this->getReference(TypeVenteFixture::TYPE_VENTE_BON_COMMANDE_REFERENCE, TypeVente::class); // Décommentez si utilisé

        /** @var Planche $plancheA */
        $plancheA = $this->getReference(PlancheFixture::PLANCHE_A_REFERENCE, Planche::class);
        /** @var Planche $plancheB */
        $plancheB = $this->getReference(PlancheFixture::PLANCHE_B_REFERENCE, Planche::class);
        /** @var Planche $plancheFratrie1 */
        $plancheFratrie1 = $this->getReference(PlancheFixture::PLANCHE_FRATRIE1_REFERENCE, Planche::class);

        // Prise de Vue 1 (Photographe 1, Ecole Lilas)
        $pdv1 = new PriseDeVue();
        $pdv1->setDate($faker->dateTimeBetween('-1 month', '+1 month'))
            ->setPhotographe($photographe1->getEmail()) // Utiliser l'email comme identifiant du photographe
            ->setEcole($ecoleLilas)
            ->setTypePrise($typePriseIndividuel)
            ->setTheme($themeNoel)
            ->setNombreEleves($faker->numberBetween(50, 150))
            ->setNombreClasses($faker->numberBetween(2, 6))
            ->setTypeVente($typeVenteInternet)
            ->setPrixEcole((string)$faker->randomFloat(2, 200, 500))
            ->setPrixParent((string)$faker->randomFloat(2, 20, 50))
            ->addPlanchesIndividuel($plancheA)
            ->addPlanchesIndividuel($plancheB)
            ->addPlanchesFratrie($plancheFratrie1)
            ->setCommentaire('Prise de vue de Noël pour les Lilas.');
        $manager->persist($pdv1);
        $this->addReference('pdv-photographe1-ecoleLilas', $pdv1);

        // Prise de Vue 2 (Photographe 2, Ecole Roses)
        $pdv2 = new PriseDeVue();
        $pdv2->setDate($faker->dateTimeBetween('-2 weeks', '+2 weeks'))
            ->setPhotographe($photographe2->getEmail())
            ->setEcole($ecoleRoses) // Ajout de l'école pour la cohérence
            ->setTypePrise($typePriseIndividuel) // Ajout type prise
            ->setTheme($themeNoel) // Ajout theme
            ->setNombreEleves($faker->numberBetween(30, 100)) // Ajout nombre élèves
            ->setNombreClasses($faker->numberBetween(1, 4)) // Ajout nombre classes
            ->setTypeVente($typeVenteInternet) // Ajout type vente
            ->setPrixEcole((string)$faker->randomFloat(2, 150, 400)) // Ajout prix école
            ->setPrixParent((string)$faker->randomFloat(2, 15, 45)) // Ajout prix parent
            ->addPlanchesIndividuel($plancheA) // Ajout planches
            ->setCommentaire('Séance de printemps pour les Roses.');
        $manager->persist($pdv2);
        $this->addReference('pdv-photographe2-ecoleRoses', $pdv2);

        // Prise de Vue 3 (Photographe 1, Ecole Roses, pour tester filtres)
        $pdv3 = new PriseDeVue();
        $pdv3->setDate($faker->dateTimeBetween('-3 days', '+3 days'))
            ->setPhotographe($photographe1->getEmail()) // Photographe 1
            ->setEcole($ecoleRoses) // Ecole Roses
            ->setTypePrise($typePriseIndividuel)
            ->setTheme($themeNoel) // ou $themePrintemps si défini et utilisé
            ->setNombreEleves($faker->numberBetween(60, 120))
            ->setNombreClasses($faker->numberBetween(2, 5))
            ->setTypeVente($typeVenteInternet) // ou $typeVenteBonCommande
            ->setPrixEcole((string)$faker->randomFloat(2, 180, 450))
            ->setPrixParent((string)$faker->randomFloat(2, 18, 48))
            ->addPlanchesIndividuel($plancheB)
            ->setCommentaire('Autre séance pour photographe 1 à l\'école des Roses.');
        $manager->persist($pdv3);
        $this->addReference('pdv-photographe1-ecoleRoses', $pdv3);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            EcoleFixture::class,
            ThemeFixture::class,
            TypePriseFixture::class,
            TypeVenteFixture::class,
            PlancheFixture::class,
        ];
    }
}