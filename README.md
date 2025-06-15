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

## Prérequis pour l'environnement Docker (Recommandé)

- Docker
- Docker Compose

## Installation et Lancement avec Docker (Recommandé)

1.  **Cloner le projet :**
    ```bash
    git clone git@github.com:votre-compte/studio_prunelle.git # Remplacez par l'URL de votre dépôt
    cd studio_prunelle
    ```

2.  **(Optionnel) Configuration locale spécifique :**
    Si vous avez besoin de surcharger des variables d'environnement spécifiques pour votre poste (non recommandé si vous utilisez la configuration Docker fournie), vous pouvez créer un fichier `.env.local`. Ce fichier n'est pas versionné.
    ```bash
    cp .env .env.local # Par exemple, puis modifiez .env.local
    ```
    *Note : Pour l'environnement Docker, les variables d'environnement clés comme `DATABASE_URL` et `APP_SECRET` sont déjà configurées dans `docker-compose.yml`.*

3.  **Construire les images Docker :**
    (Surtout nécessaire après des modifications du `Dockerfile` ou la première fois)
    ```bash
    docker-compose build
    ```

4.  **Démarrer les services Docker :**
    ```bash
    docker-compose up -d
    ```
    Cela démarrera les conteneurs pour l'application PHP, le serveur web Nginx, la base de données MySQL et PhpMyAdmin.

5.  **Installer les dépendances Composer (si non fait par le build ou pour mettre à jour) :**
    Normalement, `composer install` est exécuté lors du `docker-compose build`. Si vous avez besoin de le relancer :
    ```bash
    docker-compose exec app composer install
    ```

6.  **Exécuter les migrations de la base de données :**
    ```bash
    docker-compose exec app php bin/console doctrine:migrations:migrate
    ```
    Répondez `yes` si une confirmation est demandée.

7.  **(Optionnel) Charger les données de test (fixtures) :**
    ```bash
    docker-compose exec app php bin/console doctrine:fixtures:load
    ```
    Répondez `yes` si une confirmation est demandée.

8.  **Accéder à l'application :**
    *   Application Studio Prunelle : [http://localhost:8000](http://localhost:8000)
    *   PhpMyAdmin (gestion base de données) : [http://localhost:8081](http://localhost:8081)
        *   Serveur : `db`
        *   Utilisateur (root) : `root` / Mot de passe : `secret`
        *   Utilisateur (applicatif) : `user_prunelle` / Mot de passe : `password_prunelle`

## Commandes Docker courantes

*   **Arrêter les services :**
    ```bash
    docker-compose down
    ```
*   **Voir les logs d'un service (ex: app) :**
    ```bash
    docker-compose logs -f app
    ```
*   **Exécuter une commande dans le conteneur `app` (ex: tests PHPUnit) :**
    ```bash
    docker-compose exec app php vendor/bin/phpunit
    ```
*   **Exécuter une commande Symfony console :**
    ```bash
    docker-compose exec app php bin/console <votre_commande>
    ```
    Exemple : `docker-compose exec app php bin/console cache:clear`

*   **Reconstruire une image spécifique (ex: app) :**
    ```bash
    docker-compose build app
    ```

## Développement des assets (JavaScript/CSS)

Les assets sont gérés avec Webpack Encore.
*   **Pour compiler les assets pour le développement :**
    ```bash
    docker-compose exec app npm run dev
    ```
*   **Pour surveiller les changements et recompiler automatiquement :**
    ```bash
    docker-compose exec app npm run watch
    ```
*   **Pour compiler les assets pour la production (fait lors du `docker-compose build`) :**
    ```bash
    docker-compose exec app npm run build
    ```

---

## Installation Manuelle (Sans Docker - Moins Recommandé pour la collaboration)

Si vous ne souhaitez pas utiliser Docker, vous pouvez suivre ces étapes (assurez-vous d'avoir PHP, Composer, Node.js, NPM et un serveur MySQL configurés localement).

- **Prérequis locaux :**
    - PHP 8.2 ou supérieur (avec extensions `pdo_mysql`, `intl`, `zip`, `gd`, `opcache`, `mbstring`, `xml`, `exif`, `bcmath`, `sockets`)
    - MySQL 8.0 ou supérieur
    - Composer
    - Symfony CLI (optionnel, pour `symfony server:start`)
    - Node.js et NPM

- **Étapes :**
    ```bash
    # 1. Cloner le dépôt (si pas déjà fait)
    # git clone ...
    # cd studio_prunelle

    # 2. Configurer .env.local
    cp .env .env.local
    # Modifiez .env.local pour y mettre votre DATABASE_URL locale, par exemple :
    # DATABASE_URL="mysql://root:votre_mot_de_passe_mysql@127.0.0.1:3306/prunelle?serverVersion=8.4&charset=utf8mb4"

    # 3. Installer les dépendances PHP
    composer install

    # 4. Installer les dépendances Node.js
    npm install

    # 5. Créer la base de données (si elle n'existe pas)
    php bin/console doctrine:database:create

    # 6. Exécuter les migrations
    php bin/console doctrine:migrations:migrate

    # 7. (Optionnel) Charger les fixtures
    php bin/console doctrine:fixtures:load

    # 8. Compiler les assets
    npm run build # ou npm run dev / npm run watch

    # 9. Démarrer le serveur de développement Symfony
    symfony server:start
    # Ou utilisez votre configuration Apache/Nginx locale.
    ```

## Tests

Les commandes de test suivantes peuvent être exécutées localement ou via Docker (`docker-compose exec app ...`).

*   **Exécuter tous les tests :**
    ```bash
    php vendor/bin/phpunit
    ```
*   **Exécuter les tests d'une suite spécifique :**
    ```bash
    php vendor/bin/phpunit --testsuite Unit
    ```
*   **Générer un rapport de couverture de code HTML :**
    ```bash
    php vendor/bin/phpunit --coverage-html=var/coverage-report
    ```
    (Assurez-vous que Xdebug est configuré pour la couverture si vous exécutez localement).

