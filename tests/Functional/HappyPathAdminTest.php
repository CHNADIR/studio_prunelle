<?php

declare(strict_types=1); // PSR-12: Ajout de strict_types

namespace App\Tests\Functional;

// Ordonnancement des use (exemple, à affiner selon vos conventions exactes)
// Dépendances PHP/Symfony/Vendor
use DateTime; // Ajouté car utilisé plus bas
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
// MODIFICATION ICI : Utiliser la classe concrète ORMDatabaseTool
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

// Dépendances de l'application (App)
use App\DataFixtures\EcoleFixture;
use App\DataFixtures\PlancheFixture;
use App\DataFixtures\ThemeFixture;
use App\DataFixtures\TypePriseFixture;
use App\DataFixtures\TypeVenteFixture;
use App\DataFixtures\UserFixture;
use App\Entity\Planche; // Ajouté car utilisé dans array_map
use App\Repository\EcoleRepository;
use App\Repository\PlancheRepository;
use App\Repository\PriseDeVueRepository;
use App\Repository\ThemeRepository;
use App\Repository\TypePriseRepository;
use App\Repository\TypeVenteRepository;
use App\Repository\UserRepository;


/**
 * @group functional
 */
class HappyPathAdminTest extends WebTestCase
{
    private $client; // Pourrait être typé : private ?AbstractBrowser $client = null; si vous utilisez BrowserKitAssertionsTrait
    // MODIFICATION ICI : Mettre à jour le type de la propriété vers la classe concrète
    private ?ORMDatabaseTool $databaseTool;
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp(); // PSR-12 / Bonnes pratiques : Appel à parent::setUp()

        $this->client = static::createClient();
        $container = static::getContainer();

        // L'assignation qui causait l'erreur
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();
        $this->entityManager = $container->get(EntityManagerInterface::class);

        // MODIFICATION ICI : Utiliser les constantes de classe pour les fixtures
        $this->databaseTool->loadFixtures([
            UserFixture::class,
            ThemeFixture::class,
            TypePriseFixture::class,
            TypeVenteFixture::class,
            PlancheFixture::class,
            EcoleFixture::class,
        ]);
    }

    public function testAdminWorkflow(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $ecoleRepository = static::getContainer()->get(EcoleRepository::class);
        $themeRepository = static::getContainer()->get(ThemeRepository::class);
        $typePriseRepository = static::getContainer()->get(TypePriseRepository::class);
        $typeVenteRepository = static::getContainer()->get(TypeVenteRepository::class);
        $plancheRepository = static::getContainer()->get(PlancheRepository::class);
        $priseDeVueRepository = static::getContainer()->get(PriseDeVueRepository::class);


        // 1. Login as Admin
        $adminUser = $userRepository->findOneByEmail('admin@studioprunelle.fr');
        $this->assertNotNull($adminUser, "L'utilisateur admin 'admin@studioprunelle.fr' n'a pas été trouvé. Vérifiez UserFixture.");
        $this->client->loginUser($adminUser);

        // 2. Go to Ecole creation page and create an Ecole
        $crawler = $this->client->request('GET', '/ecole/new');
        $this->assertResponseIsSuccessful();
        // S'assurer que l'assertion pour le h1 est en français
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle école');

        $ecoleCode = 'HP999';
        $ecoleNom = 'École Happy Path';
        // Adapter le sélecteur de bouton au texte français du bouton de création
        $formEcole = $crawler->selectButton('Créer')->form([
            'ecole_form[code]' => $ecoleCode,
            'ecole_form[genre]' => 'Primaire HP',
            'ecole_form[nom]' => $ecoleNom,
            'ecole_form[rue]' => '1 Rue du Bonheur',
            'ecole_form[ville]' => 'Happytown',
            'ecole_form[codePostal]' => '00099',
            'ecole_form[telephone]' => '0908070605',
            'ecole_form[email]' => 'happy@path.com',
            'ecole_form[active]' => true,
        ]);
        $this->client->submit($formEcole);
        $this->assertResponseRedirects('/ecole/', Response::HTTP_SEE_OTHER);
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'École créée avec succès.');
        $nouvelleEcole = $ecoleRepository->findOneBy(['code' => $ecoleCode]);
        $this->assertNotNull($nouvelleEcole, "L'école Happy Path n'a pas été trouvée en BDD.");

        // 3. Go to PriseDeVue creation page
        $crawler = $this->client->request('GET', '/prise/de/vue/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle prise de vue');

        // 4. Get related entities from fixtures for PriseDeVue form
        $theme = $themeRepository->findOneBy([]);
        $typePrise = $typePriseRepository->findOneBy([]);
        $typeVente = $typeVenteRepository->findOneBy([]);
        
        $planchesIndividuel = $plancheRepository->findBy(['categorie' => 'individuel'], [], 2);
        $planchesFratrie = $plancheRepository->findBy([
            'categorie' => 'fratrie',
            'nom' => '1 photo 18x24'  // Exactement le même nom que dans la fixture
        ], [], 1);
        $this->assertCount(1, $planchesFratrie, "La planche fratrie '1 photo 18x24' n'a pas été trouvée.");
        $idPlancheFratrieSoumise = $planchesFratrie[0]->getId();
        echo "ID Planche Fratrie soumise par le test : " . $idPlancheFratrieSoumise . "\n";

        $formPourDebug = $crawler->selectButton('Créer')->form();
        
        // Pour les champs 'expanded' et 'multiple', on récupère les valeurs des cases à cocher
        $fieldFratrieDebugNodes = $formPourDebug['prise_de_vue_form[planchesFratrie]']; // Accès comme un tableau
        
        $availableOptionsValues = [];
        if ($fieldFratrieDebugNodes instanceof \Symfony\Component\DomCrawler\Field\ChoiceFormField) {
            // Cas d'un select simple ou select multiple non expanded
            $availableOptionsValues = $fieldFratrieDebugNodes->availableOptionValues();
        } elseif (is_array($fieldFratrieDebugNodes)) {
            // Cas de checkboxes (expanded = true, multiple = true)
            // Chaque élément du tableau est un ChoiceFormField représentant une checkbox
            foreach ($fieldFratrieDebugNodes as $checkboxField) {
                if ($checkboxField instanceof \Symfony\Component\DomCrawler\Field\ChoiceFormField) {
                    // La valeur d'une checkbox individuelle est sa propre valeur
                    $availableOptionsValues[] = $checkboxField->getValue();
                }
            }
        }
        
        echo "Options disponibles dans le formulaire pour planchesFratrie : " . implode(', ', $availableOptionsValues) . "\n";

        $photographeNom = 'Admin Photographe';
        $datePriseDeVue = new DateTime('+2 weeks');

        $formPriseDeVue = $crawler->selectButton('Créer')->form([
            'prise_de_vue_form[date]' => $datePriseDeVue->format('Y-m-d\TH:i'),
            'prise_de_vue_form[photographe]' => $photographeNom,
            'prise_de_vue_form[nombreEleves]' => 120,
            'prise_de_vue_form[nombreClasses]' => 4,
            'prise_de_vue_form[ecole]' => $nouvelleEcole->getId(),
            'prise_de_vue_form[typePrise]' => $typePrise->getId(),
            'prise_de_vue_form[theme]' => $theme->getId(),
            'prise_de_vue_form[typeVente]' => $typeVente->getId(),
            'prise_de_vue_form[planchesIndividuel]' => array_map(fn(Planche $p) => $p->getId(), $planchesIndividuel),
            'prise_de_vue_form[planchesFratrie]' => [$idPlancheFratrieSoumise], // S'assurer que c'est un tableau pour les champs multiples
            'prise_de_vue_form[prixEcole]' => '300.00',
            'prise_de_vue_form[prixParent]' => '30.00',
            'prise_de_vue_form[commentaire]' => 'Prise de vue pour l\'école Happy Path.',
        ]);
        $this->client->submit($formPriseDeVue);

        // Modifiez cette ligne pour supprimer le slash final
        $this->assertResponseRedirects('/prise/de/vue', Response::HTTP_SEE_OTHER); 
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue créée avec succès.');

        // 5. Verify PriseDeVue was created
        $nouvellePriseDeVue = $priseDeVueRepository->findOneBy([
            'photographe' => $photographeNom,
            'ecole' => $nouvelleEcole
        ]);
        $this->assertNotNull($nouvellePriseDeVue, "La prise de vue pour Happy Path n'a pas été trouvée.");
        $this->assertEquals($datePriseDeVue->format('Y-m-d H:i'), $nouvellePriseDeVue->getDate()->format('Y-m-d H:i'));
        $this->assertCount(count($planchesIndividuel), $nouvellePriseDeVue->getPlanchesIndividuel());
        $this->assertCount(count($planchesFratrie), $nouvellePriseDeVue->getPlanchesFratrie());


        // 6. Go to the "fiche école" of the newly created Ecole
        $crawler = $this->client->request('GET', '/ecole/' . $nouvelleEcole->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $nouvelleEcole->getNom());

        // 7. Verify the newly created PriseDeVue is listed on the "fiche école"
        $this->assertSelectorTextContains(
            'body', 
            $datePriseDeVue->format('d/m/Y')
        );
        $this->assertSelectorTextContains('body', $theme->getNom());
        $this->assertSelectorTextContains('body', $typePrise->getNom());
        $this->assertSelectorTextContains('body', number_format((float)$nouvellePriseDeVue->getPrixParent(), 2, ',', ''));


        // 8. (Optionnel) Test de la recherche d'école
        $crawler = $this->client->request('GET', '/ecole/');
        
        // Utiliser selectButton() est plus fiable pour trouver un formulaire
        $formRecherche = $crawler->selectButton('Rechercher')->form(['search' => $ecoleNom]);
        
        // Si selectButton ne fonctionne pas, essayez cette alternative :
        // $formRecherche = $crawler->filter('form')->form(['search' => $ecoleNom]);
        
        $crawler = $this->client->submit($formRecherche);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', $ecoleNom);
        $this->assertSelectorTextNotContains('table', 'Une Autre Ecole Qui Ne Devrait Pas Etre La');

    }

    protected function tearDown(): void
    {
        parent::tearDown(); // PSR-12 / Bonnes pratiques : Appel à parent::tearDown()
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        $this->client = null;
        $this->databaseTool = null;
    }
}
// PSR-12: Pas de balise PHP fermante ?>