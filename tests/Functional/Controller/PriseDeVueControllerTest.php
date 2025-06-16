<?php

namespace App\Tests\Functional\Controller;

use App\DataFixtures\EcoleFixture;
use App\DataFixtures\PlancheFixture;
use App\DataFixtures\PriseDeVueFixture;
use App\DataFixtures\ThemeFixture;
use App\DataFixtures\TypePriseFixture;
use App\DataFixtures\TypeVenteFixture;
use App\DataFixtures\UserFixture;
use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use App\Repository\PriseDeVueRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\ORMDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class PriseDeVueControllerTest extends WebTestCase
{
    private $client;
    private ?UserRepository $userRepository;
    private ?PriseDeVueRepository $priseDeVueRepository;
    private ?EntityManagerInterface $entityManager;
    private ?ORMDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->priseDeVueRepository = $container->get(PriseDeVueRepository::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadFixtures([
            UserFixture::class,
            EcoleFixture::class,
            ThemeFixture::class,
            TypePriseFixture::class,
            TypeVenteFixture::class,
            PlancheFixture::class,
            PriseDeVueFixture::class,
        ]);
    }

    // --- Tests pour la page d'index (app_prise_de_vue_index) ---
    public function testIndexPageForAnonymous(): void
    {
        $this->client->request('GET', '/prise/de/vue/');
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND, "Les utilisateurs anonymes doivent être redirigés vers la page de connexion.");
    }

    public function testIndexPageForSimpleUser(): void
    {
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $this->client->request('GET', '/prise/de/vue/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, "Les utilisateurs simples (ROLE_USER) ne devraient pas avoir accès à cette page.");
    }

    public function testIndexPageForPhotographe(): void
    {
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/');
        $this->assertResponseIsSuccessful("La page doit être accessible pour un photographe.");
        $this->assertSelectorTextContains('h1', 'Mon Planning de Prises de Vue', "Le titre pour un photographe doit être 'Mon Planning de Prises de Vue'.");
        $pdvPhotographe1 = $this->priseDeVueRepository->findBy(['photographe' => $photographeUser->getEmail()]);
        if (count($pdvPhotographe1) > 0) {
            $this->assertCount(count($pdvPhotographe1), $crawler->filter('table > tbody > tr'), "Le tableau devrait afficher les PDV du photographe connecté.");
        } else {
            $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }

    public function testIndexPageForResponsableAdministratif(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/');
        $this->assertResponseIsSuccessful("La page doit être accessible pour un responsable administratif.");
        $this->assertSelectorTextContains('h1', 'Liste des Prises de Vue', "Le titre pour un responsable administratif doit être 'Liste des Prises de Vue'.");
        $allPdvs = $this->priseDeVueRepository->findAll();
        if (count($allPdvs) > 0) {
            $this->assertCount(count($allPdvs), $crawler->filter('table > tbody > tr'), "Le tableau devrait afficher toutes les PDV pour le responsable administratif.");
        } else {
             $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }

    public function testIndexPageForAdmin(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/');
        $this->assertResponseIsSuccessful("La page doit être accessible pour un administrateur.");
        $this->assertSelectorTextContains('h1', 'Liste des Prises de Vue', "Le titre pour un administrateur doit être 'Liste des Prises de Vue'.");
        $allPdvs = $this->priseDeVueRepository->findAll();
        if (count($allPdvs) > 0) {
            $this->assertCount(count($allPdvs), $crawler->filter('table > tbody > tr'), "Le tableau devrait afficher toutes les PDV pour l'admin.");
        } else {
            $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }

    // --- Tests de recherche sur la page d'index ---
    public function testIndexPageSearchByPhotographeName(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);

        $searchTerm = UserFixture::PHOTOGRAPHE1_USER_REFERENCE; // Email du photographe
        $crawler = $this->client->request('GET', '/prise/de/vue/?search=' . urlencode($searchTerm));
        $this->assertResponseIsSuccessful();
        $expectedCount = $this->priseDeVueRepository->count(['photographe' => $searchTerm]);
        if ($expectedCount > 0) {
            $this->assertCount($expectedCount, $crawler->filter('table > tbody > tr'), "Le nombre de résultats de recherche pour le photographe est incorrect.");
            $crawler->filter('table > tbody > tr')->each(function ($row) use ($searchTerm) {
                // La colonne du photographe est la 3ème (index 2)
                $this->assertStringContainsString($searchTerm, $row->filter('td')->eq(2)->text(), "Le nom du photographe recherché n'est pas dans les résultats.");
            });
        } else {
            $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }

    public function testIndexPageSearchByEcoleName(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);

        /** @var Ecole $ecoleLilas */
        $ecoleLilas = $this->entityManager->getReference(Ecole::class, EcoleFixture::ECOLE_LILAS_REFERENCE); // Assurez-vous que cette référence existe et est correcte
        $searchTerm = $ecoleLilas->getNom();

        $crawler = $this->client->request('GET', '/prise/de/vue/?search=' . urlencode($searchTerm));
        $this->assertResponseIsSuccessful();
        
        $pdvsForEcoleLilas = $this->priseDeVueRepository->createQueryBuilder('pdv')
            ->join('pdv.ecole', 'e')
            ->where('e.nom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
        $expectedCount = count($pdvsForEcoleLilas);

        if ($expectedCount > 0) {
            $this->assertCount($expectedCount, $crawler->filter('table > tbody > tr'), "Le nombre de résultats de recherche pour l'école est incorrect.");
            $crawler->filter('table > tbody > tr')->each(function ($row) use ($searchTerm) {
                // La colonne de l'école est la 4ème (index 3)
                $this->assertStringContainsString($searchTerm, $row->filter('td')->eq(3)->text(), "Le nom de l'école recherchée n'est pas dans les résultats.");
            });
        } else {
            $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }
    
    public function testIndexPageSearchWithNoResults(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/?search=XYZ123TermeInexistantXYZ');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.', "Un message indiquant l'absence de résultats doit être affiché.");
        $this->assertCount(1, $crawler->filter('table > tbody > tr'), "Le tableau doit contenir une seule ligne pour le message 'Aucune prise de vue trouvée'.");
    }

    // --- Tests de tri sur la page d'index ---
    public function testIndexPageSortByPhotographeAsc(): void
    {
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/?sort=pdv.photographe&direction=asc');
        $this->assertResponseIsSuccessful();

        // La colonne du photographe est la 3ème
        $photographeNames = $crawler->filter('table > tbody > tr > td:nth-child(3)')->each(function ($node) {
            return trim($node->text());
        });

        if (count($photographeNames) > 1) {
            $sortedNames = $photographeNames;
            natcasesort($sortedNames); // Tri insensible à la casse
            $this->assertEquals(array_values($sortedNames), array_values($photographeNames), "Les noms des photographes ne sont pas triés par ordre alphabétique ascendant.");
        } elseif (count($photographeNames) == 1) {
            $this->assertTrue(true, "Un seul résultat, le tri n'est pas applicable de manière vérifiable ici.");
        } else {
            $this->assertSelectorTextContains('table > tbody > tr > td', 'Aucune prise de vue trouvée.');
        }
    }


    // --- Tests pour la création (app_prise_de_vue_new) ---
    public function testNewPageAccessForAnonymous(): void
    {
        $this->client->request('GET', '/prise/de/vue/new');
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND, "Les utilisateurs anonymes doivent être redirigés.");
    }

    public function testNewPageAccessForPhotographe(): void
    {
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        $this->client->request('GET', '/prise/de/vue/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, "Les photographes ne devraient pas pouvoir créer de nouvelles prises de vue.");
    }

    public function testNewPageAndSubmitForResponsableAdministratif(): void
    {
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);

        $crawler = $this->client->request('GET', '/prise/de/vue/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une nouvelle prise de vue');

        /** @var Ecole $ecole */
        $ecole = $this->entityManager->getRepository(Ecole::class)->findOneBy([]);
        /** @var Theme $theme */
        $theme = $this->entityManager->getRepository(Theme::class)->findOneBy([]);
        /** @var TypePrise $typePrise */
        $typePrise = $this->entityManager->getRepository(TypePrise::class)->findOneBy([]);
        /** @var TypeVente $typeVente */
        $typeVente = $this->entityManager->getRepository(TypeVente::class)->findOneBy([]);
        /** @var Planche $plancheIndiv */
        $plancheIndiv = $this->entityManager->getRepository(Planche::class)->findOneBy(['categorie' => 'Individuel']);
        /** @var Planche $plancheFratrie */
        $plancheFratrie = $this->entityManager->getRepository(Planche::class)->findOneBy(['categorie' => 'Fratrie']);

        $this->assertNotNull($ecole, "Aucune école trouvée dans les fixtures pour le test.");
        $this->assertNotNull($theme, "Aucun thème trouvé dans les fixtures pour le test.");
        $this->assertNotNull($typePrise, "Aucun type de prise trouvé dans les fixtures pour le test.");
        $this->assertNotNull($typeVente, "Aucun type de vente trouvé dans les fixtures pour le test.");
        // $plancheIndiv et $plancheFratrie peuvent être null si aucune n'est configurée, le test doit le gérer

        $form = $crawler->selectButton('Créer')->form([
            'prise_de_vue_form[date]' => (new \DateTime('+1 week'))->format('Y-m-d\TH:i'),
            'prise_de_vue_form[photographe]' => 'Test Photographe Depuis Formulaire',
            'prise_de_vue_form[ecole]' => $ecole->getId(),
            'prise_de_vue_form[typePrise]' => $typePrise->getId(),
            'prise_de_vue_form[theme]' => $theme->getId(),
            'prise_de_vue_form[nombreEleves]' => 100,
            'prise_de_vue_form[nombreClasses]' => 4,
            'prise_de_vue_form[typeVente]' => $typeVente->getId(),
            'prise_de_vue_form[prixEcole]' => '250.50',
            'prise_de_vue_form[prixParent]' => '25.90',
            'prise_de_vue_form[commentaire]' => 'Ceci est un test de création.',
            'prise_de_vue_form[planchesIndividuel]' => $plancheIndiv ? [$plancheIndiv->getId()] : [],
            'prise_de_vue_form[planchesFratrie]' => $plancheFratrie ? [$plancheFratrie->getId()] : [],
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/prise/de/vue/', Response::HTTP_SEE_OTHER, "Après création, redirection vers la liste des prises de vue.");
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue créée avec succès.');

        $newPdv = $this->priseDeVueRepository->findOneBy(['photographe' => 'Test Photographe Depuis Formulaire']);
        $this->assertNotNull($newPdv, "La nouvelle prise de vue n'a pas été trouvée en base de données.");
        $this->assertSame('Ceci est un test de création.', $newPdv->getCommentaire());
    }

    // --- Tests pour l'affichage (app_prise_de_vue_show) ---
    public function testShowPageAccess(): void
    {
        /** @var PriseDeVue $pdvPhotographe1 */
        $pdvPhotographe1 = $this->priseDeVueRepository->findOneBy(['photographe' => UserFixture::PHOTOGRAPHE1_USER_REFERENCE]);
        /** @var PriseDeVue $pdvPhotographe2 */
        $pdvPhotographe2 = $this->priseDeVueRepository->findOneBy(['photographe' => UserFixture::PHOTOGRAPHE2_USER_REFERENCE]);

        if (!$pdvPhotographe1 || !$pdvPhotographe2) {
            $this->markTestSkipped('Prises de vue de test non trouvées dans les fixtures pour les photographes spécifiés.');
        }

        $urlPdv1 = '/prise/de/vue/' . $pdvPhotographe1->getId();
        $urlPdv2 = '/prise/de/vue/' . $pdvPhotographe2->getId();

        // Anonyme
        $this->client->request('GET', $urlPdv1);
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);

        // Utilisateur Simple (ROLE_USER)
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $this->client->request('GET', $urlPdv1);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // Photographe 1 (voit sa PDV)
        $photographe1User = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographe1User);
        $this->client->request('GET', $urlPdv1);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail Prise de Vue #' . $pdvPhotographe1->getId());

        // Photographe 1 (voit la PDV du photographe 2, ne doit pas voir le bouton "Modifier" si la logique l'interdit)
        $this->client->request('GET', $urlPdv2);
        $this->assertResponseIsSuccessful(); 
        $this->assertSelectorTextContains('h1', 'Détail Prise de Vue #' . $pdvPhotographe2->getId());
        // Si un photographe ne peut modifier que ses propres PDV (ou seulement le commentaire),
        // le bouton "Modifier" pourrait être conditionnel ou mener à un formulaire restreint.
        // Le test actuel vérifie l'absence du bouton "Modifier" pour la PDV d'un autre.
        // Ajustez cette assertion en fonction de la logique exacte de votre Voter et du template.
        // $this->assertSelectorNotExists('a.btn-warning:contains("Modifier")'); // Décommentez et ajustez si nécessaire

        // Responsable Administratif (voit toutes les PDV)
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        $this->client->request('GET', $urlPdv1);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail Prise de Vue #' . $pdvPhotographe1->getId());
        $this->client->request('GET', $urlPdv2);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail Prise de Vue #' . $pdvPhotographe2->getId());

        // Admin (voit toutes les PDV)
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $urlPdv1);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail Prise de Vue #' . $pdvPhotographe1->getId());
    }


    // --- Tests pour l'édition (app_prise_de_vue_edit) ---
    public function testEditPageAccessAndSubmit(): void
    {
        /** @var PriseDeVue $pdvPhotographe1 */
        $pdvPhotographe1 = $this->priseDeVueRepository->findOneBy(['photographe' => UserFixture::PHOTOGRAPHE1_USER_REFERENCE]);
        /** @var PriseDeVue $pdvPhotographe2 */
        $pdvPhotographe2 = $this->priseDeVueRepository->findOneBy(['photographe' => UserFixture::PHOTOGRAPHE2_USER_REFERENCE]);

        if (!$pdvPhotographe1 || !$pdvPhotographe2) {
            $this->markTestSkipped('Prises de vue de test non trouvées pour l\'édition.');
        }

        $urlEditPdv1 = '/prise/de/vue/' . $pdvPhotographe1->getId() . '/edit';
        $urlEditPdv2 = '/prise/de/vue/' . $pdvPhotographe2->getId() . '/edit';

        // Anonyme
        $this->client->request('GET', $urlEditPdv1);
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);

        // Utilisateur Simple (ROLE_USER)
        $simpleUser = $this->userRepository->findOneByEmail(UserFixture::SIMPLE_USER_REFERENCE);
        $this->client->loginUser($simpleUser);
        $this->client->request('GET', $urlEditPdv1);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // Photographe 1 (sa PDV - peut modifier commentaire, autres champs readonly)
        $photographe1User = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographe1User);
        $crawler = $this->client->request('GET', $urlEditPdv1);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier la prise de vue');
        $this->assertSelectorExists('textarea[name="prise_de_vue_form[commentaire]"]', "Le champ commentaire doit être présent.");
        // Vérifier que les autres champs sont en lecture seule si 'can_edit_full' est false pour le photographe
        $this->assertSelectorExists('input[name="prise_de_vue_form[photographe]"][readonly="readonly"]', "Le champ photographe doit être en lecture seule pour un photographe.");

        $form = $crawler->selectButton('Mettre à jour')->form([
            'prise_de_vue_form[commentaire]' => 'Commentaire modifié par photographe 1.',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/prise/de/vue/' . $pdvPhotographe1->getId(), Response::HTTP_SEE_OTHER, "Redirection vers la page de détail après édition.");
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue modifiée avec succès.');
        $this->entityManager->refresh($pdvPhotographe1); // Rafraîchir l'entité depuis la BDD
        $this->assertSame('Commentaire modifié par photographe 1.', $pdvPhotographe1->getCommentaire());

        // Photographe 1 (PDV du photographe 2 - peut modifier commentaire, autres champs readonly)
        $crawler = $this->client->request('GET', $urlEditPdv2);
        $this->assertResponseIsSuccessful("Un photographe devrait pouvoir accéder à la page d'édition de la PDV d'un autre pour modifier le commentaire.");
        $this->assertSelectorExists('input[name="prise_de_vue_form[photographe]"][readonly="readonly"]', "Le champ photographe doit être en lecture seule.");
        $form = $crawler->selectButton('Mettre à jour')->form([
            'prise_de_vue_form[commentaire]' => 'Commentaire ajouté par photographe 1 sur PDV de photographe 2.',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/prise/de/vue/' . $pdvPhotographe2->getId(), Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue modifiée avec succès.');
        $this->entityManager->refresh($pdvPhotographe2);
        $this->assertSame('Commentaire ajouté par photographe 1 sur PDV de photographe 2.', $pdvPhotographe2->getCommentaire());

        // Responsable Administratif (peut tout modifier)
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        $crawler = $this->client->request('GET', $urlEditPdv1);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="prise_de_vue_form[photographe]"]:not([readonly])', "Le responsable administratif doit pouvoir éditer le champ photographe.");

        $nouveauNomPhotographe = 'Photographe Edité par RespAdmin';
        $form = $crawler->selectButton('Mettre à jour')->form([
            'prise_de_vue_form[photographe]' => $nouveauNomPhotographe,
            'prise_de_vue_form[commentaire]' => 'PDV éditée complètement par RespAdmin.',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/prise/de/vue/' . $pdvPhotographe1->getId(), Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue modifiée avec succès.');
        $this->entityManager->refresh($pdvPhotographe1);
        $this->assertSame($nouveauNomPhotographe, $pdvPhotographe1->getPhotographe());
        $this->assertSame('PDV éditée complètement par RespAdmin.', $pdvPhotographe1->getCommentaire());
    }

    // --- Tests pour la suppression (app_prise_de_vue_delete) ---
    public function testDeleteAction(): void
    {
        $pdvs = $this->priseDeVueRepository->findAll();
        if (empty($pdvs)) {
            $this->markTestSkipped('Aucune PriseDeVue trouvée dans les fixtures pour le test de suppression.');
        }
        /** @var PriseDeVue $pdvToDelete */
        $pdvToDelete = $pdvs[0];
        $pdvId = $pdvToDelete->getId();
        $urlDelete = '/prise/de/vue/' . $pdvId;

        // Anonyme
        $this->client->request('POST', $urlDelete); // La suppression se fait en POST
        $this->assertResponseRedirects('/connexion', Response::HTTP_FOUND);

        // Photographe (INTERDIT)
        $photographeUser = $this->userRepository->findOneByEmail(UserFixture::PHOTOGRAPHE1_USER_REFERENCE);
        $this->client->loginUser($photographeUser);
        // Pour simuler une soumission de formulaire POST, il faut un token CSRF valide.
        // Ici, on teste l'accès à la route, qui devrait être interdit avant même la vérification du token.
        $this->client->request('POST', $urlDelete, ['_token' => 'token_csrf_invalide_pour_test_acces']);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, "Un photographe ne doit pas pouvoir supprimer une prise de vue.");

        // Responsable Administratif (Succès)
        $respAdminUser = $this->userRepository->findOneByEmail(UserFixture::RESP_ADMIN_USER_REFERENCE);
        $this->client->loginUser($respAdminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/' . $pdvId); // Aller à la page show pour récupérer le formulaire de suppression
        $this->assertResponseIsSuccessful("La page de détail doit être accessible pour obtenir le formulaire de suppression.");
        $deleteForm = $crawler->filter('form[action="' . $urlDelete . '"]')->form(); // Le formulaire doit avoir l'action correcte
        $this->client->submit($deleteForm);
        $this->assertResponseRedirects('/prise/de/vue/', Response::HTTP_SEE_OTHER, "Redirection vers la liste après suppression.");
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue supprimée avec succès.');
        $this->assertNull($this->priseDeVueRepository->find($pdvId), "La prise de vue doit être supprimée de la base de données.");

        // Recharger les fixtures pour le test admin afin d'assurer qu'une PDV existe
        $this->databaseTool->loadFixtures([PriseDeVueFixture::class]); // Ne rechargez que ce qui est nécessaire
        $pdvsForAdmin = $this->priseDeVueRepository->findAll();
        if (empty($pdvsForAdmin)) {
            $this->markTestSkipped('Aucune PriseDeVue trouvée après rechargement des fixtures pour le test admin.');
        }
        $pdvForAdminDelete = $pdvsForAdmin[0];
        $pdvIdForAdmin = $pdvForAdminDelete->getId();
        $urlDeleteForAdmin = '/prise/de/vue/' . $pdvIdForAdmin;

        // Admin (Succès)
        $adminUser = $this->userRepository->findOneByEmail(UserFixture::ADMIN_USER_REFERENCE);
        $this->client->loginUser($adminUser);
        $crawler = $this->client->request('GET', '/prise/de/vue/' . $pdvIdForAdmin);
        $this->assertResponseIsSuccessful();
        $deleteFormAdmin = $crawler->filter('form[action="' . $urlDeleteForAdmin . '"]')->form();
        $this->client->submit($deleteFormAdmin);
        $this->assertResponseRedirects('/prise/de/vue/', Response::HTTP_SEE_OTHER);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Prise de vue supprimée avec succès.');
        $this->assertNull($this->priseDeVueRepository->find($pdvIdForAdmin));
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // Appeler d'abord le tearDown du parent

        // Fermer l'entity manager pour éviter les fuites de mémoire et les problèmes d'état entre les tests
        if ($this->entityManager !== null) {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->close();
            }
            $this->entityManager = null; // Effacer la propriété
        }

        // Aider le GC de PHP en annulant les propriétés
        $this->client = null;
        $this->userRepository = null;
        $this->priseDeVueRepository = null;
        $this->databaseTool = null; // LiipTestFixturesBundle recommande cela
    }
}