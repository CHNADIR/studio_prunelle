<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Repository\PriseDeVueRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PriseDeVueRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;
    private ?PriseDeVueRepository $priseDeVueRepository;

    private Ecole $ecole1;
    private Ecole $ecole2;
    private Ecole $ecole3; // Ecole without PriseDeVue

    private TypePrise $typePriseIndividuel;
    private TypePrise $typePriseGroupe;

    private Theme $themeNature;
    private Theme $themeEcole;

    private TypeVente $typeVenteInternet;
    private TypeVente $typeVenteBonCommande;

    private Planche $plancheSouvenir;
    private Planche $plancheIdentite;


    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->priseDeVueRepository = $container->get(PriseDeVueRepository::class);

        // Les entités sont créées ici pour les tests qui les utilisent globalement.
        // tearDown s'assurera du nettoyage.
        $this->createAndPersistEntities();
    }

    private function createAndPersistEntities(): void
    {
        // Create Ecoles
        $this->ecole1 = (new Ecole())
            ->setCode('ECO01') // Code unique
            ->setNom('École des Lilas')
            ->setGenre('Maternelle')
            ->setRue('1 rue des Fleurs')
            ->setVille('Paris')
            ->setCodePostal('75001')
            ->setTelephone('0123456789')
            ->setEmail('lilas@ecole.fr')
            ->setActive(true);
        $this->entityManager->persist($this->ecole1);

        $this->ecole2 = (new Ecole())
            ->setCode('ECO02') // Code unique
            ->setNom('École des Roses')
            ->setGenre('Primaire')
            ->setRue('2 avenue des Champs')
            ->setVille('Lyon')
            ->setCodePostal('69002')
            ->setTelephone('0456789012')
            ->setEmail('roses@ecole.fr')
            ->setActive(true);
        $this->entityManager->persist($this->ecole2);

        $this->ecole3 = (new Ecole())
            ->setCode('ECO03') // Code unique
            ->setNom('École des Marguerites (sans PDV)')
            ->setGenre('Collège')
            ->setRue('3 boulevard des Arts')
            ->setVille('Marseille')
            ->setCodePostal('13003')
            ->setTelephone('0678901234')
            ->setEmail('marguerites@ecole.fr')
            ->setActive(true);
        $this->entityManager->persist($this->ecole3);

        // Create TypePrise
        $this->typePriseIndividuel = (new TypePrise())->setNom('Individuel TestRepo');
        $this->entityManager->persist($this->typePriseIndividuel);
        $this->typePriseGroupe = (new TypePrise())->setNom('Groupe TestRepo');
        $this->entityManager->persist($this->typePriseGroupe);

        // Create Theme
        $this->themeNature = (new Theme())->setNom('Nature TestRepo');
        $this->entityManager->persist($this->themeNature);
        $this->themeEcole = (new Theme())->setNom('École TestRepo');
        $this->entityManager->persist($this->themeEcole);

        // Create TypeVente
        $this->typeVenteInternet = (new TypeVente())->setNom('Internet TestRepo');
        $this->entityManager->persist($this->typeVenteInternet);
        $this->typeVenteBonCommande = (new TypeVente())->setNom('Bon de Commande TestRepo');
        $this->entityManager->persist($this->typeVenteBonCommande);

        // Create Planche
        $this->plancheSouvenir = (new Planche())->setNom('Planche Souvenir TestRepo')->setCategorie('Individuel');
        $this->entityManager->persist($this->plancheSouvenir);
        $this->plancheIdentite = (new Planche())->setNom('Planche Identités TestRepo')->setCategorie('Individuel');
        $this->entityManager->persist($this->plancheIdentite);


        // Create PriseDeVue instances for ecole1
        for ($i = 1; $i <= 7; $i++) {
            $pdv = (new PriseDeVue())
                ->setEcole($this->ecole1)
                ->setDate(new DateTime("-{$i} days"))
                ->setPhotographe('Photographe A')
                ->setTypePrise($this->typePriseIndividuel)
                ->setTheme($this->themeNature)
                ->setNombreEleves(20 + $i) // Donne 21 à 27, incluant 25
                ->setNombreClasses(1)
                ->setTypeVente($this->typeVenteInternet)
                ->setPrixEcole('100.00')
                ->setPrixParent('10.00')
                ->addPlanchesIndividuel($this->plancheSouvenir);
            $this->entityManager->persist($pdv);
        }

        // Create PriseDeVue instances for ecole2
        for ($i = 1; $i <= 3; $i++) {
            $pdv = (new PriseDeVue())
                ->setEcole($this->ecole2)
                ->setDate(new DateTime("-{$i} weeks"))
                ->setPhotographe('Photographe B')
                ->setTypePrise($this->typePriseGroupe)
                ->setTheme($this->themeEcole)
                ->setNombreEleves(15 + $i) // Donne 16, 17, 18
                ->setNombreClasses(1)
                ->setTypeVente($this->typeVenteBonCommande)
                ->setPrixEcole('120.00')
                ->setPrixParent('12.00')
                ->addPlanchesIndividuel($this->plancheIdentite);
            $this->entityManager->persist($pdv);
        }
        $this->entityManager->flush(); // Flush all persisted entities
    }


    public function testFindRecentByEcole(): void
    {
        // Test 1: Default limit (5 as per PriseDeVueRepository::findRecentByEcole)
        $resultDefaultLimit = $this->priseDeVueRepository->findRecentByEcole($this->ecole1);
        $this->assertCount(5, $resultDefaultLimit, "Should return 5 PDVs by default limit.");
        // Check if they are for ecole1
        foreach ($resultDefaultLimit as $pdv) {
            $this->assertSame($this->ecole1->getId(), $pdv->getEcole()->getId(), "PDV should belong to ecole1.");
        }
        // Check order (most recent first)
        for ($i = 0; $i < count($resultDefaultLimit) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $resultDefaultLimit[$i + 1]->getDate(),
                $resultDefaultLimit[$i]->getDate(),
                "PDVs should be ordered by date DESC."
            );
        }

        // Test 2: Custom limit (e.g., 3)
        $limit = 3;
        $resultCustomLimit = $this->priseDeVueRepository->findRecentByEcole($this->ecole1, $limit);
        $this->assertCount($limit, $resultCustomLimit, "Should return $limit PDVs with custom limit.");
        foreach ($resultCustomLimit as $pdv) {
            $this->assertSame($this->ecole1->getId(), $pdv->getEcole()->getId());
        }

        // Test 3: Limit greater than available PDVs for ecole1 (has 7)
        $limitLarge = 10;
        $resultLargeLimit = $this->priseDeVueRepository->findRecentByEcole($this->ecole1, $limitLarge);
        $this->assertCount(7, $resultLargeLimit, "Should return all 7 PDVs for ecole1 when limit is larger.");

        // Test 4: Ecole with no PriseDeVue
        $resultNoPdvs = $this->priseDeVueRepository->findRecentByEcole($this->ecole3);
        $this->assertCount(0, $resultNoPdvs, "Should return 0 PDVs for an ecole with no PDVs.");

        // Test 5: Ensure it only returns PDVs for the specified ecole (ecole1, not ecole2)
        $allPdvsEcole1 = $this->priseDeVueRepository->findRecentByEcole($this->ecole1, 100); // Get all for ecole1
        foreach ($allPdvsEcole1 as $pdv) {
            $this->assertNotSame($this->ecole2->getId(), $pdv->getEcole()->getId(), "PDV from ecole2 should not be in results for ecole1.");
        }
    }

    public function testStandardFindBy(): void
    {
        // Data specific to this test
        $ecoleFindBy = $this->createEcoleLocal('ECO04', 'École FindBy Test');
        $themeFindBy = $this->createThemeLocal('Theme FindBy Local');
        $typePriseFindBy = $this->createTypePriseLocal('TypePrise FindBy Local');
        $typeVenteFindBy = $this->createTypeVenteLocal('TypeVente FindBy Local');
        $plancheForFindBy = $this->createPlancheLocal('Planche For FindBy', 'Individuel');


        $pdvX1 = $this->createPriseDeVueLocal($ecoleFindBy, new \DateTime('2023-04-01'), 'Photographe X FindBy', $themeFindBy, $typePriseFindBy, $typeVenteFindBy, 20, $plancheForFindBy);
        $pdvY = $this->createPriseDeVueLocal($ecoleFindBy, new \DateTime('2023-04-02'), 'Photographe Y FindBy', $themeFindBy, $typePriseFindBy, $typeVenteFindBy, 25, $plancheForFindBy);
        $pdvX2 = $this->createPriseDeVueLocal($ecoleFindBy, new \DateTime('2023-04-03'), 'Photographe X FindBy', $themeFindBy, $typePriseFindBy, $typeVenteFindBy, 30, $plancheForFindBy);

        $this->entityManager->flush(); // Flush data specific to this test

        // Test find by photographe (specific to $ecoleFindBy)
        $resultPhotographeX = $this->priseDeVueRepository->findBy(['ecole' => $ecoleFindBy, 'photographe' => 'Photographe X FindBy']);
        $this->assertCount(2, $resultPhotographeX);
        $idsPhotographeX = array_map(fn($pdv) => $pdv->getId(), $resultPhotographeX);
        $this->assertContains($pdvX1->getId(), $idsPhotographeX);
        $this->assertContains($pdvX2->getId(), $idsPhotographeX);

        $resultPhotographeY = $this->priseDeVueRepository->findBy(['ecole' => $ecoleFindBy, 'photographe' => 'Photographe Y FindBy']);
        $this->assertCount(1, $resultPhotographeY);
        $this->assertSame($pdvY->getId(), $resultPhotographeY[0]->getId());

        // Test find by a non-existent photographe for this ecole
        $resultNonExistent = $this->priseDeVueRepository->findBy(['ecole' => $ecoleFindBy, 'photographe' => 'Photographe ZZZ FindBy']);
        $this->assertCount(0, $resultNonExistent);

        // Test find by nombreEleves (specific to $ecoleFindBy)
        // This was the failing assertion. Now it's specific to $ecoleFindBy.
        $resultNbEleves = $this->priseDeVueRepository->findBy(['ecole' => $ecoleFindBy, 'nombreEleves' => 25]);
        $this->assertCount(1, $resultNbEleves, "Should find 1 PDV for ecoleFindBy with 25 eleves.");
        $this->assertSame($pdvY->getId(), $resultNbEleves[0]->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform();

            if ($platform->getName() === 'mysql') {
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
            }

            $tableNames = [
                'prise_individuel_planches',
                'prise_fratrie_planches',
                'prise_de_vue',
                'planche',
                'ecole',
                'theme',
                'type_prise',
                'type_vente',
                // 'user', // Décommentez si vos tests créent des User
            ];

            foreach ($tableNames as $tableName) {
                try {
                    // La méthode getTruncateTableSQL s'attend à ce que la table existe.
                    // Pour plus de robustesse, on pourrait vérifier l'existence avec $schemaManager->tablesExist([$tableName])
                    $truncateQuery = $platform->getTruncateTableSQL($tableName, true /* cascade */);
                    $connection->executeStatement($truncateQuery);
                } catch (\Exception $e) {
                    // Gérer l'erreur si la table n'existe pas ou autre problème
                    // echo "Warning: Could not truncate table $tableName: " . $e->getMessage() . "\n";
                }
            }

            if ($platform->getName() === 'mysql') {
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
            }

            $this->entityManager->clear(); // Détache toutes les entités
            $this->entityManager->close();
            $this->entityManager = null;
        }
        $this->priseDeVueRepository = null;
    }

    // Méthodes d'aide locales pour testStandardFindBy pour éviter les conflits de noms/codes
    private function createEcoleLocal(string $code, string $nom): Ecole
    {
        $ecole = (new Ecole())
            ->setCode($code)
            ->setNom($nom)
            ->setGenre('Test Genre Local')
            ->setRue('Test Rue Local')
            ->setVille('Testville Local')
            ->setCodePostal(substr(str_replace('-', '', $code), 0, 5))
            ->setTelephone('0199999999')
            ->setEmail(strtolower(str_replace([' ', '.'], '', $nom)) . '@local.example.com')
            ->setActive(true);
        $this->entityManager->persist($ecole);
        return $ecole;
    }

    private function createThemeLocal(string $nom): Theme
    {
        $theme = (new Theme())->setNom($nom);
        $this->entityManager->persist($theme);
        return $theme;
    }

    private function createTypePriseLocal(string $nom): TypePrise
    {
        $typePrise = (new TypePrise())->setNom($nom);
        $this->entityManager->persist($typePrise);
        return $typePrise;
    }

    private function createTypeVenteLocal(string $nom): TypeVente
    {
        $typeVente = (new TypeVente())->setNom($nom);
        $this->entityManager->persist($typeVente);
        return $typeVente;
    }
    
    private function createPlancheLocal(string $nom, string $categorie): Planche
    {
        $planche = (new Planche())->setNom($nom)->setCategorie($categorie);
        $this->entityManager->persist($planche);
        return $planche;
    }

    private function createPriseDeVueLocal(Ecole $ecole, DateTime $date, string $photographe, Theme $theme, TypePrise $typePrise, TypeVente $typeVente, int $nbEleves, Planche $planche): PriseDeVue
    {
        $pdv = (new PriseDeVue())
            ->setEcole($ecole)
            ->setDate($date)
            ->setPhotographe($photographe)
            ->setTypePrise($typePrise)
            ->setTheme($theme)
            ->setNombreEleves($nbEleves)
            ->setNombreClasses(1) // Simplification
            ->setTypeVente($typeVente)
            ->setPrixEcole('150.00') // Valeurs exemples
            ->setPrixParent('15.00')  // Valeurs exemples
            ->addPlanchesIndividuel($planche); // Assurez-vous que la relation est correcte
        $this->entityManager->persist($pdv);
        return $pdv;
    }
}