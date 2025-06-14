# Studio Prunelle - Application de Gestion de Prises de Vue Scolaires

![Code Coverage](https://img.shields.io/badge/Coverage-59%25-yellow.svg)
![PHPUnit](https://img.shields.io/badge/PHPUnit-11.5-blue.svg)
![Symfony](https://img.shields.io/badge/Symfony-6.4-success.svg)

## Description

Studio Prunelle est une application web dédiée à la gestion des prises de vue photographiques en milieu scolaire. Elle permet aux photographes et administrateurs de gérer les séances photo, les écoles clientes et les planches photo proposées.

## Fonctionnalités principales

- Gestion des écoles (ajout, modification, consultation)
- Organisation des prises de vue avec dates et informations détaillées
- Gestion des planches photo individuelles et de fratrie
- Système d'utilisateurs avec différents niveaux d'accès (admin, photographe)
- Gestion sécurisée des images uploadées
- Interface de recherche et filtrage des données

## Prérequis

- PHP 8.2 ou supérieur
- MySQL 8.0 ou supérieur
- Composer
- Symfony CLI
- Node.js et NPM (pour les assets)
- Xdebug (pour les rapports de couverture de code)

## Installation

```bash
# Cloner le dépôt
git clone git@github.com:votre-compte/studio_prunelle.git
cd studio_prunelle

# Installer les dépendances
composer install
npm install

# Configurer la base de données dans [.env.local](http://_vscodecontentref_/0)
# DATABASE_URL=mysql://user:password@127.0.0.1:3306/studio_prunelle

# Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les fixtures pour les données de test
php bin/console doctrine:fixtures:load

# Compiler les assets
npm run build

# Démarrer le serveur de développement
symfony server:start

# Démarrer les services Docker
docker-compose up -d
# Lance les conteneurs définis dans docker-compose.yml (comme MySQL, PHPMyAdmin)

# Arrêter les services Docker
docker-compose down
# Arrête les conteneurs actifs

# Installer des extensions PHP nécessaires
sudo apt install php8.2-mbstring
sudo apt install php8.2-xml
sudo apt install php8.2-xdebug
# Installation des extensions PHP requises pour les tests et la couverture de code

# Activer une extension PHP
sudo phpenmod -v 8.2 mbstring
# Active l'extension mbstring pour PHP 8.2 spécifiquement
````

# Exécuter tous les tests
php bin/phpunit
# Lance l'exécution de tous les tests définis dans phpunit.dist.xml

# Exécuter les tests d'une suite spécifique
php bin/phpunit --testsuite Unit
# Lance uniquement les tests de la suite "Unit"

# Exécuter les tests d'un fichier spécifique
php bin/phpunit tests/Unit/Service/UploaderHelperTest.php
# Lance uniquement les tests contenus dans UploaderHelperTest.php

# Exécuter les tests d'un repository spécifique
php bin/phpunit tests/Unit/Repository/PriseDeVueRepositoryTest.php
# Lance uniquement les tests du repository PriseDeVue

# Générer un rapport TestDox HTML
php bin/phpunit --testdox-html=docs/testdox.html
# Génère une documentation HTML à partir des noms des méthodes de test

# Générer un rapport de couverture de code HTML
php bin/phpunit --coverage-html=var/coverage-report
# Génère un rapport détaillé de la couverture de code en format HTML

# Création de la base de données de test
php bin/console doctrine:schema:update --env=test --force
# Met à jour le schéma de base de données de test à partir des entités Doctrine

# Exécution du serveur de développement
symfony server:start
# Démarre le serveur web local de Symfony pour tester l'application

# Vérifier la version de PHP
php --version
# Affiche la version de PHP utilisée

# Lister les extensions PHP installées
php -m
# Affiche toutes les extensions PHP actuellement chargées

# Vérifier la présence d'une extension spécifique
php -m | grep xdebug
# Vérifie si l'extension Xdebug est installée et active
````

