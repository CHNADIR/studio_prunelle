<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\EcoleFixture; // Assurez-vous que EcoleFixture est bien importée et utilisée
use App\DataFixtures\UserFixture;  // Assurez-vous que UserFixture est bien importée et utilisée
use App\Entity\Ecole;
use App\Repository\EcoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class EcoleControllerTest extends WebTestCase
{
    private $client;
    private ?UserRepository $userRepository;
    private ?EcoleRepository $ecoleRepository;
    private ?EntityManagerInterface $entityManager;
    private ?ORMDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->ecoleRepository = $container->get(EcoleRepository::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadFixtures([
            UserFixture::class,
            EcoleFixture::class,
        ]);
    }

    public function testIndexPageForAnonymous(): void
    {
        $this->client->request('GET', '/ecole/');
        // Route ^/ecole/$ requiert ROLE_RESPONSABLE_ADMINISTRATIF
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);
    }

    public function testIndexPageForSimpleUser(): void // Utilisateur avec ROLE_USER
    {
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);

        $this->client->request('GET', '/ecole/');
        // Route ^/ecole/$ requiert ROLE_RESPONSABLE_ADMINISTRATIF, ROLE_USER ne suffit pas
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testIndexPageForPhotographeUser(): void
    {
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);

        $this->client->request('GET', '/ecole/');
        // Route ^/ecole/$ requiert ROLE_RESPONSABLE_ADMINISTRATIF, ROLE_PHOTOGRAPHE ne suffit pas
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testIndexPageForResponsableAdministratifUser(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);

        $this->client->request('GET', '/ecole/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des écoles');
    }

    public function testIndexPageForAdminUser(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/ecole/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des écoles');
    }

    // --- Tests pour la création (new) ---
    public function testNewEcolePageAccessForAnonymous(): void
    {
        $this->client->request('GET', '/ecole/new');
        // Route ^/ecole/new requiert ROLE_RESPONSABLE_ADMINISTRATIF
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);
    }

    public function testNewEcolePageAccessForSimpleUser(): void
    {
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $this->client->request('GET', '/ecole/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    public function testNewEcolePageAccessForPhotographeUser(): void
    {
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        $this->client->request('GET', '/ecole/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    private function submitNewEcoleForm(string $buttonText = 'Créer'): void
    {
        $form = $this->client->getCrawler()->selectButton($buttonText)->form([
            'ecole_form[code]' => 'TESTN1',
            'ecole_form[genre]' => 'Maternelle Test New',
            'ecole_form[nom]' => 'École Test New Form',
            'ecole_form[rue]' => '1 Rue du Test New',
            'ecole_form[ville]' => 'Testville New',
            'ecole_form[codePostal]' => '00002',
            'ecole_form[telephone]' => '0102030406',
            'ecole_form[email]' => 'newecoleform@test.com',
            'ecole_form[active]' => true,
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/ecole/', Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'École créée avec succès.');

        $newEcole = $this->ecoleRepository->findOneBy(['code' => 'TESTN1']);
        $this->assertNotNull($newEcole);
        $this->assertSame('École Test New Form', $newEcole->getNom());
    }

    public function testNewEcolePageAccessAndSubmitForResponsableAdministratif(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);

        $this->client->request('GET', '/ecole/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle école');
        $this->submitNewEcoleForm();
    }

    public function testNewEcolePageAccessAndSubmitForAdmin(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/ecole/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle école');
        $this->submitNewEcoleForm('Créer'); // Le bouton peut être "Créer" ou "Enregistrer"
    }


    // --- Tests pour l'affichage (show) ---
    public function testShowEcolePageAccess(): void
    {
        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) {
            $this->markTestSkipped('Aucune école trouvée dans les fixtures pour le test show.');
        }
        $url = '/ecole/' . $ecole->getId();

        // Anonymous
        $this->client->request('GET', $url);
        // Route ^/ecole/\d+$ requiert ROLE_PHOTOGRAPHE
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND); // Ligne 141 originale, l'attente est correcte.

        // ROLE_USER (simple)
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $this->client->request('GET', $url);
        // ROLE_USER n'est pas ROLE_PHOTOGRAPHE
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // ROLE_PHOTOGRAPHE
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Fiche École: ' . $ecole->getNom());

        // ROLE_RESPONSABLE_ADMINISTRATIF
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Fiche École: ' . $ecole->getNom());

        // ROLE_ADMIN
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Fiche École: ' . $ecole->getNom());
    }

    // --- Tests pour l'édition (edit) ---
    private function submitEditEcoleForm(Ecole $ecole, string $updatedNom, string $buttonText = 'Mettre à jour'): void
    {
        $form = $this->client->getCrawler()->selectButton($buttonText)->form([
            'ecole_form[nom]' => $updatedNom,
            'ecole_form[code]' => $ecole->getCode(),
            'ecole_form[genre]' => $ecole->getGenre(),
            'ecole_form[rue]' => $ecole->getRue(),
            'ecole_form[ville]' => $ecole->getVille(),
            'ecole_form[codePostal]' => $ecole->getCodePostal(),
            'ecole_form[telephone]' => $ecole->getTelephone(),
            'ecole_form[email]' => $ecole->getEmail(),
            'ecole_form[active]' => $ecole->isActive(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/ecole/', Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'École modifiée avec succès.');

        $this->entityManager->refresh($ecole); // Recharger l'entité
        $this->assertSame($updatedNom, $ecole->getNom());
    }
    
    public function testEditEcolePageAccessForAnonymous(): void
    {
        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');
        $this->client->request('GET', '/ecole/' . $ecole->getId() . '/edit');
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);
    }

    public function testEditEcolePageAccessForSimpleUser(): void
    {
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');
        $this->client->request('GET', '/ecole/' . $ecole->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    public function testEditEcolePageAccessForPhotographeUser(): void
    {
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');
        $this->client->request('GET', '/ecole/' . $ecole->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditEcolePageAccessAndSubmitForResponsableAdministratif(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);

        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');
        
        $this->client->request('GET', '/ecole/' . $ecole->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier École: ' . $ecole->getNom());
        $this->submitEditEcoleForm($ecole, 'École Éditée par RespAdmin');
    }

    public function testEditEcolePageAccessAndSubmitForAdmin(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);

        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');

        $this->client->request('GET', '/ecole/' . $ecole->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier École: ' . $ecole->getNom());
        $this->submitEditEcoleForm($ecole, 'École Test Editée Admin', 'Mettre à jour');
    }


    // --- Tests pour la suppression (delete) ---
    private function performDeleteEcole(Ecole $ecole): void
    {
        $ecoleId = $ecole->getId();
        $crawler = $this->client->request('GET', '/ecole/' . $ecoleId); // Aller sur la page show pour le formulaire
        $this->assertResponseIsSuccessful();

        // S'assurer que le formulaire de suppression existe pour cet utilisateur
        $deleteFormSelector = 'form[action$="/ecole/' . $ecoleId . '"] button.btn-danger';
        $this->assertSelectorExists($deleteFormSelector);
        
        $form = $crawler->filter($deleteFormSelector)->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/ecole/', Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'École supprimée avec succès.');

        $this->assertNull($this->ecoleRepository->find($ecoleId));
    }

    public function testDeleteEcoleActionForResponsableAdministratif(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        
        $ecole = $this->ecoleRepository->findOneByCode('69002'); // École des Roses par exemple
        if (!$ecole) $this->markTestSkipped('École spécifique pour suppression non trouvée.');
        
        $this->performDeleteEcole($ecole);
    }

    public function testDeleteEcoleActionForAdmin(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        
        $ecole = $this->ecoleRepository->findOneByCode('75001'); // École des Lilas par exemple
        if (!$ecole) $this->markTestSkipped('École spécifique pour suppression non trouvée.');

        $this->performDeleteEcole($ecole);
    }

    public function testDeleteEcoleActionForbiddenForOtherRoles(): void
    {
        $rolesToTest = [
            UserFixture::SIMPLE_USER_REFERENCE => 'simple user',
            UserFixture::PHOTOGRAPHE1_USER_REFERENCE => 'photographe'
        ];

        $ecole = $this->ecoleRepository->findOneBy([]);
        if (!$ecole) $this->markTestSkipped('Aucune école.');
        $urlShow = '/ecole/' . $ecole->getId();

        foreach ($rolesToTest as $userReference => $roleName) {
            $user = $this->userRepository->findOneByEmail($userReference);
            $this->client->loginUser($user);
            
            $crawler = $this->client->request('GET', $urlShow);
            $this->assertResponseIsSuccessful(); // Ils peuvent voir la page show
            // Vérifier que le formulaire/bouton de suppression n'est PAS présent
            $this->assertSelectorNotExists('form[action$="/ecole/' . $ecole->getId() . '"] button.btn-danger', "Le bouton supprimer ne devrait pas être visible pour $roleName.");

            // Tenter une requête POST directe devrait aussi être interdit
            $this->client->request('POST', '/ecole/' . $ecole->getId());
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, "La suppression POST devrait être interdite pour $roleName.");
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        $this->client = null;
        $this->userRepository = null;
        $this->ecoleRepository = null;
        $this->databaseTool = null;
    }
}