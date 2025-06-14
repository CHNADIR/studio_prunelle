<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Ecole;
use App\Entity\Planche;
use App\Entity\PriseDeVue;
use App\Entity\Theme;
use App\Entity\TypePrise;
use App\Entity\TypeVente;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationTest extends KernelTestCase
{
    private ?ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->validator = $container->get(ValidatorInterface::class);
    }

    /**
     * @dataProvider ecoleDataProvider
     */
    public function testEcoleValidation(Ecole $ecole, int $expectedViolationsCount, array $expectedMessages = []): void
    {
        $violations = $this->validator->validate($ecole);
        $this->assertCount($expectedViolationsCount, $violations);

        if ($expectedViolationsCount > 0) {
            $actualMessages = [];
            foreach ($violations as $violation) {
                $actualMessages[] = $violation->getMessage();
            }
            foreach ($expectedMessages as $expectedMessage) {
                $this->assertContains($expectedMessage, $actualMessages, "Message attendu non trouvé: '{$expectedMessage}' parmi " . implode(', ', $actualMessages));
            }
        }
    }

    public static function ecoleDataProvider(): iterable
    {
        yield 'Valid Ecole' => [
            (new Ecole())
                ->setCode('12345')
                ->setGenre('Maternelle')
                ->setNom('École des Lilas')
                ->setRue('1 rue des Fleurs')
                ->setVille('Paris')
                ->setCodePostal('75001')
                ->setTelephone('0123456789')
                ->setEmail('contact@ecoledeslilas.fr')
                ->setActive(true),
            0
        ];

        yield 'Ecole with invalid code (too long)' => [
            (new Ecole())->setCode('123456')->setGenre('Primaire')->setNom('Test')->setRue('Rue')->setVille('Ville')->setCodePostal('12345')->setTelephone('0101010101')->setEmail('test@test.com')->setActive(true),
            1,
            ["Le code ne doit pas dépasser 5 caractères."]
        ];
        yield 'Ecole with blank code' => [
            (new Ecole())->setCode('')->setGenre('Primaire')->setNom('Test')->setRue('Rue')->setVille('Ville')->setCodePostal('12345')->setTelephone('0101010101')->setEmail('test@test.com')->setActive(true),
            1,
            ["Le code de l'école ne peut pas être vide."]
        ];
        yield 'Ecole with blank nom' => [
            (new Ecole())->setCode('12345')->setGenre('Primaire')->setNom('')->setRue('Rue')->setVille('Ville')->setCodePostal('12345')->setTelephone('0101010101')->setEmail('test@test.com')->setActive(true),
            1,
            ["Le nom de l'école ne peut pas être vide."]
        ];
        // TODO: Ajoutez d'autres cas pour les champs obligatoires de Ecole (genre, rue, ville, codePostal, telephone, email, active)
        // et pour les formats (email, téléphone si regex)
    }

    /**
     * @dataProvider priseDeVueDataProvider
     */
    public function testPriseDeVueValidation(PriseDeVue $priseDeVue, int $expectedViolationsCount, array $expectedMessages = []): void
    {
        $violations = $this->validator->validate($priseDeVue);
        $this->assertCount($expectedViolationsCount, $violations);

        if ($expectedViolationsCount > 0) {
            $actualMessages = [];
            foreach ($violations as $violation) {
                $actualMessages[] = $violation->getMessage();
            }
            foreach ($expectedMessages as $expectedMessage) {
                $this->assertContains($expectedMessage, $actualMessages, "Message attendu non trouvé: '{$expectedMessage}' parmi " . implode(', ', $actualMessages));
            }
        }
    }

    public static function priseDeVueDataProvider(): iterable
    {
        $validEcole = (new Ecole())
            ->setCode('ECO01')
            ->setGenre('Maternelle')
            ->setNom('École Test Valide')
            ->setRue('1 rue Test')
            ->setVille('Testville')
            ->setCodePostal('75000')
            ->setTelephone('0102030405')
            ->setEmail('ecole@test.com')
            ->setActive(true);

        // Assumons que ces entités ont une contrainte NotBlank sur leur nom
        $validTypePrise = (new TypePrise())->setNom('Individuel');
        $validTheme = (new Theme())->setNom('Nature');
        $validTypeVente = (new TypeVente())->setNom('Internet');
        $validPlanche = (new Planche())->setNom('Planche Souvenir Standard')->setCategorie('individuel'); // Assurez-vous que la catégorie est définie si elle est obligatoire

        $getBasePriseDeVue = static function () use ($validEcole, $validTypePrise, $validTheme, $validTypeVente, $validPlanche): PriseDeVue {
            return (new PriseDeVue())
                ->setDate(new \DateTime())
                ->setPhotographe('Michel Photographe')
                ->setEcole($validEcole)
                ->setTypePrise($validTypePrise)
                ->setTheme($validTheme)
                ->setNombreEleves(25)
                ->setNombreClasses(1)
                ->setTypeVente($validTypeVente)
                ->setPrixEcole(150.00)
                ->setPrixParent(15.00)
                ->addPlanchesIndividuel($validPlanche); // MODIFIÉ ICI: addPlancheIndividuelle -> addPlanchesIndividuel
        };

        yield 'PriseDeVue complètement valide' => [
            $getBasePriseDeVue(),
            0
        ];

        // Tests pour les champs de PriseDeVue

        // Test pour date nulle
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'date');
        // $reflectionProperty->setAccessible(true); // Plus nécessaire à partir de PHP 8.1 pour les propriétés typées
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec date nulle' => [$priseDeVue, 1, ["La date est obligatoire."]];

        $priseDeVue = $getBasePriseDeVue(); // Nouvelle instance
        $priseDeVue->setPhotographe('');
        yield 'PriseDeVue avec photographe vide' => [
            $priseDeVue,
            2, // Attendre 2 violations
            [
                "Le nom du photographe est obligatoire.",
                "Le nom du photographe doit contenir au moins 2 caractères."
            ]
        ];

        $priseDeVue = $getBasePriseDeVue(); // Nouvelle instance
        $priseDeVue->setPhotographe('A');
        yield 'PriseDeVue avec photographe trop court' => [$priseDeVue, 1, ["Le nom du photographe doit contenir au moins 2 caractères."]];

        // Test pour ecole nulle
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'ecole');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec ecole nulle' => [$priseDeVue, 1, ["L'école est obligatoire."]];

        // Test pour typePrise nul
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'typePrise');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec typePrise nul' => [$priseDeVue, 1, ["Le type de prise est obligatoire."]];

        // Test pour theme nul
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'theme');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec theme nul' => [$priseDeVue, 1, ["Le thème est obligatoire."]];

        // Test pour nombreEleves nul (si setNombreEleves n'accepte pas null)
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'nombreEleves');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec nombreEleves nul' => [$priseDeVue, 1, ["Le nombre d'élèves est obligatoire."]];
        
        $priseDeVue = $getBasePriseDeVue();
        $priseDeVue->setNombreEleves(-5); // setNombreEleves accepte int, donc ceci est valide pour tester Assert\Positive
        yield 'PriseDeVue avec nombreEleves négatif' => [$priseDeVue, 1, ["Le nombre d'élèves doit être un nombre positif."]]; // Message mis à jour

        // Test pour nombreClasses nul (si setNombreClasses n'accepte pas null)
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'nombreClasses');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec nombreClasses nul' => [$priseDeVue, 1, ["Le nombre de classes est obligatoire."]];

        $priseDeVue = $getBasePriseDeVue();
        $priseDeVue->setNombreClasses(-1); // setNombreClasses accepte int
        yield 'PriseDeVue avec nombreClasses négatif' => [$priseDeVue, 1, ["Le nombre de classes doit être un nombre positif."]]; // Message mis à jour

        // Test pour typeVente nul
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'typeVente');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec typeVente nul' => [$priseDeVue, 1, ["Le type de vente est obligatoire."]];

        // Test pour prixEcole nul (si setPrixEcole n'accepte pas null)
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'prixEcole');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec prixEcole nul' => [$priseDeVue, 1, ["Le prix école est obligatoire."]];

        $priseDeVue = $getBasePriseDeVue();
        $priseDeVue->setPrixEcole("-10.00"); // setPrixEcole accepte string
        yield 'PriseDeVue avec prixEcole négatif' => [
            $priseDeVue,
            2, // Attendre 2 violations
            [
                "Le prix école doit être un nombre positif.",
                "Le prix école doit être un nombre décimal valide (ex: 10.50)."
            ]
        ];

        // Test pour prixParent nul (si setPrixParent n'accepte pas null)
        $priseDeVue = $getBasePriseDeVue();
        $reflectionProperty = new \ReflectionProperty(PriseDeVue::class, 'prixParent');
        $reflectionProperty->setValue($priseDeVue, null);
        yield 'PriseDeVue avec prixParent nul' => [$priseDeVue, 1, ["Le prix parent est obligatoire."]];

        $priseDeVue = $getBasePriseDeVue();
        $priseDeVue->setPrixParent("-0.01"); // setPrixParent accepte string
        yield 'PriseDeVue avec prixParent négatif' => [
            $priseDeVue,
            2, // Attendre 2 violations
            [
                "Le prix parent doit être un nombre positif.",
                "Le prix parent doit être un nombre décimal valide (ex: 10.50)."
            ]
        ];

        $priseDeVue = $getBasePriseDeVue();
        $priseDeVue->setPrixParent("0.00"); // Test avec zéro
        yield 'PriseDeVue avec prixParent zéro (invalide car Assert\Positive)' => [
            $priseDeVue,
            1, // Attendre 1 violation
            ["Le prix parent doit être un nombre positif."]
        ];

        // ... continuez pour les autres champs de la même manière ...
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->validator = null;
    }
}